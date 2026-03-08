<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class LocationController extends BaseApiController
{
    /**
     * List countries for browsing (id, name, code, image URL). Optional image_only to get only with images.
     */
    public function countries(Request $request): JsonResponse
    {
        $query = Country::query()->orderBy('name');
        if ($request->boolean('image_only')) {
            $query->whereNotNull('image')->where('image', '!=', '');
        }
        $countries = $query->get();
        $data = $countries->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'image' => $c->image ? URL::asset('storage/'.$c->image) : null,
        ]);
        return $this->success(['data' => $data]);
    }

    /**
     * List cities for trending destinations (id, name, country_id, country name, image URL). Optional country_id filter.
     */
    public function cities(Request $request): JsonResponse
    {
        $query = City::query()->with('country')->orderBy('name');
        if ($request->filled('country_id')) {
            $query->where('country_id', (int) $request->country_id);
        }
        if ($request->boolean('image_only')) {
            $query->whereNotNull('image')->where('image', '!=', '');
        }
        $limit = (int) $request->input('limit', 50);
        $cities = $query->limit(min($limit, 100))->get();
        $data = $cities->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'country_id' => $c->country_id,
            'country_name' => $c->country?->name,
            'image' => $c->image ? URL::asset('storage/'.$c->image) : null,
        ]);
        return $this->success(['data' => $data]);
    }
}
