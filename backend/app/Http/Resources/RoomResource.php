<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'base_price' => (float) $this->base_price,
            'total_rooms' => $this->total_rooms,
            'hotel_id' => $this->hotel_id,
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->image_url,
                        'alt_text' => $image->alt_text,
                        'is_banner' => $image->is_banner,
                        'sort_order' => $image->sort_order,
                    ];
                });
            }),
            'banner_image' => $this->whenLoaded('images', function () {
                $banner = $this->images->firstWhere('is_banner', true);
                return $banner ? [
                    'id' => $banner->id,
                    'url' => $banner->image_url,
                    'alt_text' => $banner->alt_text,
                ] : null;
            }),
        ];
    }
}
