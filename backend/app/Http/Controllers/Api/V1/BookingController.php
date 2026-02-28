<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\CreateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookingController extends BaseApiController
{
    public function __construct(
        protected BookingService $bookingService,
        protected PaymentService $paymentService,
        protected PricingService $pricingService,
    ) {}

    /**
     * Preview price breakdown (no booking created). Same body as store; returns subtotal, discount, total.
     */
    public function preview(CreateBookingRequest $request): JsonResponse
    {
        $user = $request->user();
        $hotelId = (int) $request->hotel_id;
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;
        $currency = $request->input('currency', 'USD');

        $roomQuantities = [];
        foreach ($request->rooms as $item) {
            $roomId = (int) $item['room_id'];
            $room = \App\Models\Room::where('id', $roomId)->where('hotel_id', $hotelId)->first();
            if (! $room) {
                return $this->error('Room does not belong to the selected hotel.', 422, 'INVALID_ROOM');
            }
            $roomQuantities[$roomId] = (int) $item['quantity'];
        }

        $couponCode = $request->filled('coupon_code') ? trim($request->coupon_code) : null;
        $breakdown = $this->pricingService->calculate(
            $roomQuantities,
            $checkIn,
            $checkOut,
            $hotelId,
            $couponCode,
            (int) $user->id,
        );

        return $this->success([
            'subtotal' => $breakdown->subtotal,
            'discount' => $breakdown->discount,
            'tax' => $breakdown->tax,
            'total' => $breakdown->total,
            'currency' => $breakdown->currency,
            'coupon_code' => $breakdown->couponCode,
            'coupon_applied' => $breakdown->couponId !== null,
        ]);
    }

    /**
     * Create booking: validate, lock, create; return booking + payment intent.
     */
    public function store(CreateBookingRequest $request): JsonResponse
    {
        $user = $request->user();
        $hotelId = (int) $request->hotel_id;
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;
        $currency = $request->input('currency', 'USD');

        $roomQuantities = [];
        foreach ($request->rooms as $item) {
            $roomId = (int) $item['room_id'];
            $room = \App\Models\Room::where('id', $roomId)->where('hotel_id', $hotelId)->first();
            if (! $room) {
                return $this->error('Room does not belong to the selected hotel.', 422, 'INVALID_ROOM');
            }
            $roomQuantities[$roomId] = (int) $item['quantity'];
        }

        $couponCode = $request->filled('coupon_code') ? trim($request->coupon_code) : null;

        try {
            $booking = $this->bookingService->createBooking(
                (int) $user->id,
                $hotelId,
                $roomQuantities,
                $checkIn,
                $checkOut,
                $currency,
                $couponCode
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422, 'UNAVAILABLE');
        }

        $paymentIntent = $this->paymentService->initiatePayment($booking);

        return $this->success([
            'booking' => new BookingResource($booking->load(['hotel', 'bookingRooms.room', 'coupon'])),
            'payment_intent' => $paymentIntent,
        ], 201);
    }

    /**
     * Download invoice/receipt for a booking (HTML). Customer: own bookings; Vendor: own hotel's bookings.
     */
    public function invoice(Request $request, string $uuid): Response|JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->with(['hotel', 'bookingRooms.room', 'customer', 'coupon'])->firstOrFail();
        $user = $request->user();
        $isCustomer = (int) $booking->customer_id === (int) $user->id;
        $isVendor = $booking->hotel && (int) $booking->hotel->vendor_id === (int) $user->id;
        if (! $isCustomer && ! $isVendor) {
            return $this->error('You do not have access to this invoice.', 403, 'FORBIDDEN');
        }

        $nights = $booking->check_in->diffInDays($booking->check_out);
        $subtotal = (float) $booking->total_price - (float) ($booking->tax_amount ?? 0) + (float) ($booking->discount_amount ?? 0);

        $html = view('invoice.booking', [
            'booking' => $booking,
            'nights' => $nights,
            'subtotal' => round($subtotal, 2),
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="invoice-'.$booking->uuid.'.html"',
        ]);
    }

    /**
     * Confirm booking (poll status after payment). Returns current booking state.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->where('customer_id', $request->user()->id)->firstOrFail();
        return $this->success(new BookingResource($booking->load(['hotel', 'bookingRooms.room', 'review', 'coupon'])));
    }

    /**
     * Cancel booking. Releases inventory, then refunds any completed payment via PaymentService.
     */
    public function cancel(Request $request, string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->where('customer_id', $request->user()->id)
            ->with('payments')
            ->firstOrFail();
        try {
            $this->bookingService->cancelBooking($booking);
            foreach ($booking->payments as $payment) {
                if ($payment->status === \App\Enums\PaymentStatus::COMPLETED->value && $payment->external_id) {
                    $refundable = (float) $payment->amount - (float) ($payment->refunded_amount ?? 0);
                    if ($refundable > 0) {
                        $this->paymentService->refund($payment, $refundable, 'requested_by_customer');
                    }
                }
            }
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 422, 'CANCEL_FAILED');
        }
        return $this->success(new BookingResource($booking->fresh()->load(['hotel', 'bookingRooms.room', 'coupon'])));
    }

    /**
     * Customer booking history (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $paginator = Booking::where('customer_id', $request->user()->id)
            ->with(['hotel', 'bookingRooms.room', 'coupon'])
            ->latest()
            ->paginate($perPage);

        return $this->success([
            'data' => BookingResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
