<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::query()
            ->where('approved', true)
            ->with('booking:id,hotel_id,check_in,check_out');

        if ($request->filled('hotel_id')) {
            $query->whereHas('booking', fn ($q) => $q->where('hotel_id', $request->hotel_id));
        }

        $perPage = (int) $request->input('per_page', 15);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = Booking::where('id', $validated['booking_id'])
            ->where('customer_id', $request->user()->id)
            ->where('status', 'completed')
            ->firstOrFail();

        $exists = Review::where('booking_id', $booking->id)->exists();
        if ($exists) {
            return $this->error('You have already reviewed this booking.', 422);
        }

        $review = Review::create([
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'approved' => false,
        ]);

        return $this->success(['data' => $review], 201);
    }
}
