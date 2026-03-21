<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class ItalyCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $italy = Country::where('code', 'IT')->first();
        if (! $italy) {
            return;
        }

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
            'Pisa',
            'Siena',
            'Padua',
            'Bari',
            'Catania',
            'Cagliari',
            'Modena',
            'Perugia',
            'Lecce',
            'Rimini',
            'Bergamo',
            'Trento',
            'Bolzano',
            'Sorrento',
            'Taormina',
            'Como',
            'Lucca',
            'Ravenna',
            'Parma',
            'Ferrara',
            'Vicenza',
            'Mantua',
            'Treviso',
            'Udine',
            'Trieste',
            'Livorno',
            'La Spezia',
            'Salerno',
            'Amalfi',
            'Positano',
            'Capri',
            'Sanremo',
            'Portofino',
            'Cortona',
            'Assisi',
            'Orvieto',
            'Ravello',
            'Montepulciano',
            'San Gimignano',
            'Alberobello',
            'Matera',
        ];

        foreach ($cities as $name) {
            City::firstOrCreate(
                ['country_id' => $italy->id, 'name' => $name],
                ['country_id' => $italy->id, 'name' => $name]
            );
        }
    }
}
