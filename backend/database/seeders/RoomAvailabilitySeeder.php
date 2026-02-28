<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomAvailability;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RoomAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::all();
        if ($rooms->isEmpty()) {
            return;
        }

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(180);

        foreach ($rooms as $room) {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Create realistic availability patterns
                $dayOfWeek = $date->dayOfWeek;
                $isWeekend = ($dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY);
                $isHoliday = $this->isHoliday($date);
                
                // Base availability with weekend variations
                if ($isWeekend) {
                    $availableRooms = max(1, $room->total_rooms - rand(0, 2)); // Higher demand on weekends
                } elseif ($isHoliday) {
                    $availableRooms = max(1, $room->total_rooms - rand(1, 3)); // Even higher demand on holidays
                } else {
                    $availableRooms = max($room->total_rooms - rand(0, 1), $room->total_rooms - 2); // Normal weekday availability
                }

                // Price variations based on demand
                $priceOverride = null;
                if ($isWeekend) {
                    $priceOverride = $room->base_price + rand(20, 50); // Weekend premium
                } elseif ($isHoliday) {
                    $priceOverride = $room->base_price + rand(30, 80); // Holiday premium
                } elseif (rand(1, 20) === 1) {
                    $priceOverride = $room->base_price + rand(-15, 25); // Random price fluctuations
                }

                // Seasonal pricing (higher in summer months)
                $month = $date->month;
                if (in_array($month, [6, 7, 8])) { // Summer months
                    $priceOverride = ($priceOverride ?? $room->base_price) + rand(10, 30);
                } elseif (in_array($month, [12, 1, 2])) { // Winter months
                    $priceOverride = ($priceOverride ?? $room->base_price) - rand(5, 15);
                }

                RoomAvailability::firstOrCreate(
                    [
                        'room_id' => $room->id,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'available_rooms' => $availableRooms,
                        'price_override' => $priceOverride,
                    ]
                );
            }
        }
    }

    private function isHoliday(Carbon $date): bool
    {
        // Simple holiday detection (can be expanded)
        $holidays = [
            '01-01', // New Year's Day
            '07-04', // Independence Day
            '12-25', // Christmas Day
            '11-11', // Veterans Day
            '02-14', // Valentine's Day
        ];

        return in_array($date->format('m-d'), $holidays);
    }
}
