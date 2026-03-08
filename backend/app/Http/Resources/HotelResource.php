<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    private static function timeToHi(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            return substr($value, 0, 5); // "14:00:00" -> "14:00"
        }
        return $value instanceof \DateTimeInterface ? $value->format('H:i') : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'check_in' => self::timeToHi($this->check_in),
            'check_out' => self::timeToHi($this->check_out),
            'status' => $this->status,
            'rooms' => RoomResource::collection($this->whenLoaded('rooms')),
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
            'average_rating' => $this->when(isset($this->average_rating), fn () => round((float) $this->average_rating, 2)),
            'review_count' => $this->when(isset($this->review_count), fn () => (int) $this->review_count),
            'cancellation_policy' => $this->when(
                $this->cancellation_policy !== null,
                fn () => $this->cancellation_policy
            ),
            'cancellation_policy_summary' => $this->when(
                $this->cancellation_policy !== null,
                fn () => app(\App\Services\CancellationPolicyService::class)->getSummaryForPolicy($this->cancellation_policy)
            ),
            'amenities' => $this->whenLoaded('amenities', fn () => $this->amenities->map(fn ($a) => [
                'id' => $a->id,
                'slug' => $a->slug,
                'name' => $a->name,
                'icon' => $a->icon,
            ])),
        ];
    }
}
