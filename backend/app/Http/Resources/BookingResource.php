<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status,
            'check_in' => $this->check_in?->format('Y-m-d'),
            'check_out' => $this->check_out?->format('Y-m-d'),
            'total_price' => (float) $this->total_price,
            'currency' => $this->currency,
            'subtotal' => (float) ($this->total_price - (float) ($this->tax_amount ?? 0) + (float) ($this->discount_amount ?? 0)),
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'tax_amount' => (float) ($this->tax_amount ?? 0),
            'coupon_code' => $this->when($this->coupon_id, fn () => $this->coupon?->code),
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
            'booking_rooms' => BookingRoomResource::collection($this->whenLoaded('bookingRooms')),
            'review' => new ReviewResource($this->whenLoaded('review')),
        ];
    }
}
