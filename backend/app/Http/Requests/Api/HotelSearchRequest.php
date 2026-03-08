<?php

namespace App\Http\Requests\Api;

class HotelSearchRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'city_id' => 'nullable|integer|exists:cities,id',
            'country_id' => 'nullable|integer|exists:countries,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_km' => 'nullable|numeric|min:0|max:500',
            'check_in' => 'nullable|date|after_or_equal:today',
            'check_out' => 'nullable|date|after:check_in',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'min_capacity' => 'nullable|integer|min:1|max:20',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:50',
            'sort' => 'nullable|string|in:price_low,price_high,rating,name',
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1',
        ];
    }
}
