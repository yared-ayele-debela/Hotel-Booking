<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'Italy', 'code' => 'IT'],
            ['name' => 'United States', 'code' => 'US'],
        ];

        foreach ($countries as $data) {
            Country::firstOrCreate(
                ['code' => $data['code']],
                ['name' => $data['name']]
            );
        }
    }
}
