<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\SavedHotelResource;
use App\Models\Hotel;
use App\Models\SavedHotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends BaseApiController
{
    /**
     * List current user's saved hotels.
     */
    public function index(Request $request): JsonResponse
    {
        $items = SavedHotel::where('user_id', $request->user()->id)
            ->with(['hotel.rooms'])
            ->latest()
            ->get();

        return $this->success([
            'data' => SavedHotelResource::collection($items),
        ]);
    }

    /**
     * Add hotel to wishlist. Optional check_in/check_out for future price alerts.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'hotel_id' => 'required|integer|exists:hotels,id',
            'check_in' => 'nullable|date',
            'check_out' => 'nullable|date|after_or_equal:check_in',
        ]);

        $userId = $request->user()->id;
        $hotelId = (int) $request->hotel_id;

        $hotel = Hotel::where('id', $hotelId)->where('status', 'active')->first();
        if (! $hotel) {
            return $this->error('Hotel not found or not available.', 404, 'NOT_FOUND');
        }

        $saved = SavedHotel::firstOrCreate(
            [
                'user_id' => $userId,
                'hotel_id' => $hotelId,
            ],
            [
                'check_in' => $request->filled('check_in') ? $request->check_in : null,
                'check_out' => $request->filled('check_out') ? $request->check_out : null,
            ]
        );

        if (! $saved->wasRecentlyCreated) {
            if ($request->filled('check_in') || $request->filled('check_out')) {
                $saved->update([
                    'check_in' => $request->filled('check_in') ? $request->check_in : null,
                    'check_out' => $request->filled('check_out') ? $request->check_out : null,
                ]);
            }
        }

        $saved->load(['hotel.rooms']);
        return $this->success(new SavedHotelResource($saved), 201);
    }

    /**
     * Remove hotel from wishlist.
     */
    public function destroy(Request $request, int $hotelId): JsonResponse
    {
        $deleted = SavedHotel::where('user_id', $request->user()->id)
            ->where('hotel_id', $hotelId)
            ->delete();

        if (! $deleted) {
            return $this->error('Hotel was not in your wishlist.', 404, 'NOT_FOUND');
        }

        return response()->noContent();
    }
}
