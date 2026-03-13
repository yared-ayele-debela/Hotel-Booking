<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\CreateBookingRequest;
use App\Http\Requests\Api\CreateGuestBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\CancellationPolicyService;
use App\Services\PaymentService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookingController extends BaseApiController
{
    public function __construct(
        protected BookingService $bookingService,
        protected CancellationPolicyService $cancellationPolicyService,
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
        $lateCheckout = (bool) $request->input('late_checkout', false);
        $breakdown = $this->pricingService->calculate(
            $roomQuantities,
            $checkIn,
            $checkOut,
            $hotelId,
            $couponCode,
            (int) $user->id,
            $lateCheckout,
        );

        return $this->success([
            'subtotal' => $breakdown->subtotal,
            'discount' => $breakdown->discount,
            'tax' => $breakdown->tax,
            'tax_name' => $breakdown->taxName,
            'tax_inclusive' => $breakdown->taxInclusive,
            'add_on_amount' => $breakdown->addOnAmount,
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

        return $this->success([
            'booking' => new BookingResource($booking->load(['hotel', 'bookingRooms.room', 'coupon'])),
        ], 201);
    }

    /**
     * Guest checkout: preview price (no auth). Same body as storeGuest + guest_email, guest_name.
     */
    public function previewGuest(CreateGuestBookingRequest $request): JsonResponse
    {
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
        $lateCheckout = (bool) $request->input('late_checkout', false);
        $breakdown = $this->pricingService->calculate(
            $roomQuantities,
            $checkIn,
            $checkOut,
            $hotelId,
            $couponCode,
            null,
            $lateCheckout,
        );

        return $this->success([
            'subtotal' => $breakdown->subtotal,
            'discount' => $breakdown->discount,
            'tax' => $breakdown->tax,
            'tax_name' => $breakdown->taxName,
            'tax_inclusive' => $breakdown->taxInclusive,
            'add_on_amount' => $breakdown->addOnAmount,
            'total' => $breakdown->total,
            'currency' => $breakdown->currency,
            'coupon_code' => $breakdown->couponCode,
            'coupon_applied' => $breakdown->couponId !== null,
        ]);
    }

    /**
     * Guest checkout: create booking (no auth). Requires guest_email, guest_name.
     */
    public function storeGuest(CreateGuestBookingRequest $request): JsonResponse
    {
        $hotelId = (int) $request->hotel_id;
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;
        $currency = $request->input('currency', 'USD');
        $guestEmail = $request->validated('guest_email');
        $guestName = $request->validated('guest_name');

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
        $lateCheckout = (bool) $request->input('late_checkout', false);

        try {
            $booking = $this->bookingService->createBooking(
                null,
                $hotelId,
                $roomQuantities,
                $checkIn,
                $checkOut,
                $currency,
                $couponCode,
                $guestEmail,
                $guestName,
                $lateCheckout,
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422, 'UNAVAILABLE');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422, 'INVALID');
        }

        return $this->success([
            'booking' => new BookingResource($booking->load(['hotel', 'bookingRooms.room', 'coupon'])),
            'view_booking_url' => $this->signedGuestBookingUrl($booking),
            'guest_checkout_url' => $this->signedGuestCheckoutUrl($booking),
        ], 201);
    }

    /**
     * Create Stripe Checkout session for a pending booking. Returns checkout_url for redirect.
     * Auth: customer (own booking) or guest (valid signed URL).
     */
    public function createCheckoutSession(Request $request, string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->with('hotel')->firstOrFail();

        $user = $request->user();
        $isOwner = $user && $booking->customer_id !== null && (int) $booking->customer_id === (int) $user->id;
        $isGuest = ! $user && $booking->isGuest() && $request->hasValidSignature();
        if (! $isOwner && ! $isGuest) {
            return $this->error('You do not have access to this booking.', 403, 'FORBIDDEN');
        }

        if ($booking->status !== \App\Enums\BookingStatus::PENDING_PAYMENT->value) {
            return $this->error('This booking is not awaiting payment.', 422, 'INVALID_STATUS');
        }

        try {
            $result = $this->paymentService->createCheckoutSession($booking);
            return $this->success($result);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 500, 'CHECKOUT_FAILED');
        }
    }

    /**
     * Create Stripe Checkout session for guest booking (signed URL required).
     */
    public function guestCheckoutSession(Request $request): JsonResponse
    {
        $uuid = $request->query('uuid');
        if (! $uuid) {
            return $this->error('Missing booking reference.', 422, 'MISSING_UUID');
        }
        $booking = Booking::where('uuid', $uuid)->with('hotel')->firstOrFail();
        if (! $booking->isGuest()) {
            return $this->error('This booking is not a guest booking.', 422, 'NOT_GUEST');
        }
        if ($booking->status !== \App\Enums\BookingStatus::PENDING_PAYMENT->value) {
            return $this->error('This booking is not awaiting payment.', 422, 'INVALID_STATUS');
        }
        try {
            $result = $this->paymentService->createCheckoutSession($booking);
            return $this->success($result);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 500, 'CHECKOUT_FAILED');
        }
    }

    /**
     * View a guest booking via signed URL (no auth). Query: uuid, signature, expires.
     */
    public function guestView(Request $request): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return $this->error('Invalid or expired link.', 403, 'INVALID_SIGNATURE');
        }
        $uuid = $request->query('uuid');
        if (! $uuid) {
            return $this->error('Missing booking reference.', 422, 'MISSING_UUID');
        }
        $booking = Booking::where('uuid', $uuid)->with(['hotel', 'bookingRooms.room', 'coupon'])->first();
        if (! $booking || ! $booking->isGuest()) {
            return $this->error('Booking not found or not a guest booking.', 404, 'NOT_FOUND');
        }
        return $this->success(new BookingResource($booking));
    }

    /**
     * Claim a guest booking: attach to the current user. Booking guest_email must match user email.
     */
    public function claim(Request $request, string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->with(['hotel', 'bookingRooms.room', 'coupon'])->firstOrFail();
        if ($booking->customer_id !== null) {
            return $this->error('This booking is already linked to an account.', 422, 'ALREADY_CLAIMED');
        }
        if (empty($booking->guest_email)) {
            return $this->error('This booking cannot be claimed.', 422, 'NOT_GUEST');
        }
        $user = $request->user();
        if (strtolower((string) $booking->guest_email) !== strtolower((string) $user->email)) {
            return $this->error('You can only claim bookings made with your email address.', 403, 'EMAIL_MISMATCH');
        }
        $booking->update(['customer_id' => $user->id, 'guest_email' => null, 'guest_name' => null]);
        return $this->success(new BookingResource($booking->fresh()->load(['hotel', 'bookingRooms.room', 'coupon'])));
    }

    private function signedGuestBookingUrl(Booking $booking): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'api.v1.bookings.guest-view',
            now()->addDays(30),
            ['uuid' => $booking->uuid]
        );
    }

    private function signedGuestCheckoutUrl(Booking $booking): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'api.v1.bookings.guest-checkout-session',
            now()->addDays(30),
            ['uuid' => $booking->uuid],
            true
        );
    }

    /**
     * Download invoice/receipt for a booking (HTML). Customer: own bookings; Vendor: own hotel's bookings.
     */
    public function invoice(Request $request, string $uuid): Response|JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->with(['hotel', 'bookingRooms.room', 'customer', 'coupon'])->firstOrFail();
        $user = $request->user();
        $isCustomer = $booking->customer_id !== null && (int) $booking->customer_id === (int) $user->id;
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
     * Guest invoice via signed URL (no auth). Query: uuid, signature, expires.
     */
    public function guestInvoice(Request $request): Response|JsonResponse
    {
        if (! $request->hasValidSignature()) {
            return $this->error('Invalid or expired link.', 403, 'INVALID_SIGNATURE');
        }
        $uuid = $request->query('uuid');
        if (! $uuid) {
            return $this->error('Missing booking reference.', 422, 'MISSING_UUID');
        }
        $booking = Booking::where('uuid', $uuid)->with(['hotel', 'bookingRooms.room', 'customer', 'coupon'])->first();
        if (! $booking || ! $booking->isGuest()) {
            return $this->error('Booking not found.', 404, 'NOT_FOUND');
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
        $booking = Booking::where('uuid', $uuid)->with(['hotel', 'bookingRooms.room', 'review', 'coupon'])->firstOrFail();
        $user = $request->user();
        $isOwner = $booking->customer_id !== null && (int) $booking->customer_id === (int) $user->id;
        if (! $isOwner) {
            return $this->error('You do not have access to this booking.', 403, 'FORBIDDEN');
        }
        return $this->success(new BookingResource($booking));
    }

    /**
     * Cancel booking. Releases inventory, then refunds any completed payment via PaymentService.
     */
    public function cancel(Request $request, string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->with('payments')->firstOrFail();
        $isOwner = $booking->customer_id !== null && (int) $booking->customer_id === (int) $request->user()->id;
        if (! $isOwner) {
            return $this->error('You do not have access to this booking.', 403, 'FORBIDDEN');
        }
        try {
            $refundAmount = $this->cancellationPolicyService->getRefundAmount($booking);
            $this->bookingService->cancelBooking($booking);
            if ($refundAmount > 0) {
                foreach ($booking->payments as $payment) {
                    if ($payment->status === \App\Enums\PaymentStatus::COMPLETED->value && $payment->external_id) {
                        $refundable = (float) $payment->amount - (float) ($payment->refunded_amount ?? 0);
                        if ($refundable > 0) {
                            $toRefund = min($refundAmount, $refundable);
                            $this->paymentService->refund($payment, $toRefund, 'requested_by_customer');
                            $refundAmount -= $toRefund;
                            if ($refundAmount <= 0) {
                                break;
                            }
                        }
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
