<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class ItalyCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Rome',
            'Milan',
            'Venice',
            'Florence',
            'Naples',
            'Turin',
            'Bologna',
            'Palermo',
            'Genoa',
            'Verona',
        ];

        foreach ($cities as $name) {
            City::firstOrCreate(
                ['country_id' => 1, 'name' => $name],
                ['country_id' => 1, 'name' => $name]
            );
        }
    }
}
