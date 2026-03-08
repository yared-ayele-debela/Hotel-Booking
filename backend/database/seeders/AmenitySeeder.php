<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['slug' => 'wifi', 'name' => 'Free Wi-Fi', 'icon' => 'wifi', 'sort_order' => 1],
            ['slug' => 'pool', 'name' => 'Swimming Pool', 'icon' => 'pool', 'sort_order' => 2],
            ['slug' => 'parking', 'name' => 'Free Parking', 'icon' => 'parking', 'sort_order' => 3],
            ['slug' => 'breakfast', 'name' => 'Breakfast Included', 'icon' => 'breakfast', 'sort_order' => 4],
            ['slug' => 'gym', 'name' => 'Fitness Center', 'icon' => 'gym', 'sort_order' => 5],
            ['slug' => 'spa', 'name' => 'Spa', 'icon' => 'spa', 'sort_order' => 6],
            ['slug' => 'restaurant', 'name' => 'Restaurant', 'icon' => 'restaurant', 'sort_order' => 7],
            ['slug' => 'bar', 'name' => 'Bar', 'icon' => 'bar', 'sort_order' => 8],
            ['slug' => 'air-conditioning', 'name' => 'Air Conditioning', 'icon' => 'air-conditioning', 'sort_order' => 9],
            ['slug' => 'room-service', 'name' => 'Room Service', 'icon' => 'room-service', 'sort_order' => 10],
            ['slug' => 'pet-friendly', 'name' => 'Pet Friendly', 'icon' => 'pet-friendly', 'sort_order' => 11],
            ['slug' => 'business-center', 'name' => 'Business Center', 'icon' => 'business-center', 'sort_order' => 12],
            ['slug' => 'beach-access', 'name' => 'Beach Access', 'icon' => 'beach-access', 'sort_order' => 13],
            ['slug' => 'balcony', 'name' => 'Balcony', 'icon' => 'balcony', 'sort_order' => 14],
            ['slug' => 'minibar', 'name' => 'Minibar', 'icon' => 'minibar', 'sort_order' => 15],
        ];

        foreach ($amenities as $data) {
            Amenity::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
