<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\SubmitReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends BaseApiController
{
    /**
     * Submit review: only for completed bookings; one review per booking.
     */
    public function store(SubmitReviewRequest $request): JsonResponse
    {
        $user = $request->user();
        $booking = Booking::where('id', $request->booking_id)
            ->where('customer_id', $user->id)
            ->firstOrFail();

        if ($booking->status !== 'completed' && $booking->status !== 'confirmed') {
            return $this->error('Review only allowed for completed or confirmed bookings.', 422, 'INVALID_BOOKING_STATE');
        }

        if ($booking->review()->exists()) {
            return $this->error('One review per booking.', 422, 'REVIEW_EXISTS');
        }

        $review = Review::create([
            'booking_id' => $booking->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'approved' => false,
        ]);

        return $this->success(new ReviewResource($review), 201);
    }

    /**
     * List reviews: for a hotel or for a booking. Only approved, verified (linked to booking). Paginated.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'hotel_id' => 'nullable|exists:hotels,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Review::query()->where('approved', true)->whereHas('booking');

        if ($request->filled('hotel_id')) {
            $query->whereHas('booking', fn ($q) => $q->where('hotel_id', $request->hotel_id));
        }
        if ($request->filled('booking_id')) {
            $query->where('booking_id', $request->booking_id);
        }

        $perPage = (int) $request->input('per_page', 15);
        $paginator = $query->with('booking')->latest()->paginate($perPage);

        return $this->success([
            'data' => ReviewResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
