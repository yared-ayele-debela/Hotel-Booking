<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class PlatformSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'HotelBooking',
            'default_currency' => 'USD',
            'commission_rate' => '10',
            'max_advance_booking_days' => '365',
            'min_advance_booking_days' => '0',
            'cancellation_hours_before' => '24',
            'support_email' => 'support@hotelbooking.test',
            'maintenance_mode' => '0',
        ];

        foreach ($settings as $key => $value) {
            PlatformSetting::set($key, $value);
        }
    }
}
