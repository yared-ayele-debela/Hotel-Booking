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
            'cancellation_policy' => $this->when(
                $this->cancellation_policy !== null,
                fn () => $this->cancellation_policy
            ),
            'cancellation_policy_summary' => $this->when(
                $this->cancellation_policy !== null || ($this->relationLoaded('hotel') && $this->hotel?->cancellation_policy !== null),
                function () {
                    $svc = app(\App\Services\CancellationPolicyService::class);
                    $policy = $this->cancellation_policy ?? ($this->relationLoaded('hotel') ? $this->hotel?->cancellation_policy : null);
                    return $policy !== null ? $svc->getSummaryForPolicy($policy) : null;
                }
            ),
        ];
    }
}
