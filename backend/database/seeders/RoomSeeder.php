<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = Hotel::all();
        if ($hotels->isEmpty()) {
            return;
        }

        $roomTemplates = [
            ['name' => 'Standard Double', 'capacity' => 2, 'base_price' => 89.00, 'total_rooms' => 10],
            ['name' => 'Deluxe King', 'capacity' => 2, 'base_price' => 129.00, 'total_rooms' => 6],
            ['name' => 'Suite', 'capacity' => 4, 'base_price' => 199.00, 'total_rooms' => 4],
            ['name' => 'Family Room', 'capacity' => 5, 'base_price' => 159.00, 'total_rooms' => 5],
            ['name' => 'Single', 'capacity' => 1, 'base_price' => 69.00, 'total_rooms' => 8],
        ];

        foreach ($hotels as $hotel) {
            foreach ($roomTemplates as $template) {
                Room::firstOrCreate(
                    [
                        'hotel_id' => $hotel->id,
                        'name' => $template['name'],
                    ],
                    [
                        'room_type_id' => null,
                        'capacity' => $template['capacity'],
                        'base_price' => $template['base_price'],
                        'total_rooms' => $template['total_rooms'],
                    ]
                );
            }
        }
    }
}
