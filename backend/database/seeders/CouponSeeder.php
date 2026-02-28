<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'SAVE10'],
            [
                'type' => Coupon::TYPE_PERCENTAGE,
                'value' => 10,
                'min_nights' => 1,
                'min_amount' => 50,
                'valid_from' => now()->subDay(),
                'valid_to' => now()->addMonths(3),
                'usage_limit_total' => 100,
                'usage_limit_per_user' => 1,
                'hotel_ids' => null,
                'room_ids' => null,
                'name' => '10% off',
                'description' => '10% off when you spend at least $50',
                'is_active' => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'FLAT20'],
            [
                'type' => Coupon::TYPE_FIXED,
                'value' => 20,
                'min_nights' => 2,
                'min_amount' => 100,
                'valid_from' => now()->subDay(),
                'valid_to' => null,
                'usage_limit_total' => null,
                'usage_limit_per_user' => 2,
                'hotel_ids' => null,
                'room_ids' => null,
                'name' => '$20 off',
                'description' => '$20 off stays of 2+ nights, min $100',
                'is_active' => true,
            ]
        );
    }
}
