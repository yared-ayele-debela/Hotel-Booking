<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Amenity;
use Illuminate\Http\JsonResponse;

class AmenityController extends BaseApiController
{
    /**
     * List all amenities for filter UI (slug, name, icon).
     */
    public function index(): JsonResponse
    {
        $amenities = Amenity::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'icon']);

        $data = $amenities->map(fn ($a) => [
            'id' => $a->id,
            'slug' => $a->slug,
            'name' => $a->name,
            'icon' => $a->icon,
        ]);

        return $this->success(['data' => $data]);
    }
}
