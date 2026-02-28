<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingRoomResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'room_id' => $this->room_id,
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'room' => new RoomResource($this->whenLoaded('room')),
        ];
    }
}
