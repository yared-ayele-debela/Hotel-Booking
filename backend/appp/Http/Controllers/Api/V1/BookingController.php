<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends BaseApiController
{
    public function __construct(
        protected BookingService $bookingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::where('customer_id', $request->user()->id)
            ->with(['hotel', 'bookingRooms.room'])
            ->orderByDesc('created_at')
            ->paginate((int) $request->input('per_page', 15));

        return $this->success([
            'data' => $bookings->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'rooms' => ['required', 'array', 'min:1'],
            'rooms.*.room_id' => ['required', 'integer', 'exists:rooms,id'],
            'rooms.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $booking = $this->bookingService->create(
            $request->user()->id,
            $validated['hotel_id'],
            $validated['check_in'],
            $validated['check_out'],
            $validated['rooms']
        );

        return $this->success([
            'data' => $booking->load(['hotel', 'bookingRooms.room']),
        ], 201);
    }

    public function show(string $uuid, Request $request): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)
            ->where('customer_id', $request->user()->id)
            ->with(['hotel', 'bookingRooms.room'])
            ->firstOrFail();

        return $this->success(['data' => $booking]);
    }

    public function cancel(string $uuid, Request $request): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)
            ->where('customer_id', $request->user()->id)
            ->firstOrFail();

        $this->bookingService->cancel($booking);

        return $this->success(['data' => $booking->fresh()]);
    }
}
