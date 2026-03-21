<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    /** Italian city coordinates (lat, lng) for realistic placement */
    private array $cityCoordinates = [
        'Rome' => [41.9028, 12.4964],
        'Milan' => [45.4642, 9.1900],
        'Venice' => [45.4408, 12.3155],
        'Florence' => [43.7696, 11.2558],
        'Naples' => [40.8518, 14.2681],
        'Turin' => [45.0703, 7.6869],
        'Bologna' => [44.4949, 11.3426],
        'Palermo' => [38.1157, 13.3615],
        'Genoa' => [44.4056, 8.9463],
        'Verona' => [45.4384, 10.9916],
        'Pisa' => [43.7228, 10.4017],
        'Siena' => [43.3188, 11.3308],
        'Padua' => [45.4064, 11.8768],
        'Bari' => [41.1171, 16.8719],
        'Catania' => [37.5079, 15.0830],
        'Cagliari' => [39.2238, 9.1217],
        'Modena' => [44.6471, 10.9252],
        'Perugia' => [43.1107, 12.3908],
        'Lecce' => [40.3515, 18.1750],
        'Rimini' => [44.0678, 12.5695],
        'Bergamo' => [45.6983, 9.6773],
        'Trento' => [46.0664, 11.1257],
        'Bolzano' => [46.4983, 11.3548],
        'Sorrento' => [40.6263, 14.3758],
        'Taormina' => [37.8537, 15.2930],
        'Como' => [45.8081, 9.0852],
        'Lucca' => [43.8376, 10.4950],
        'Ravenna' => [44.4184, 12.2035],
        'Parma' => [44.8015, 10.3279],
        'Ferrara' => [44.8381, 11.6198],
        'Vicenza' => [45.5455, 11.5354],
        'Mantua' => [45.1564, 10.7914],
        'Treviso' => [45.6669, 12.2430],
        'Udine' => [46.0710, 13.2345],
        'Trieste' => [45.6495, 13.7768],
        'Livorno' => [43.5485, 10.3106],
        'La Spezia' => [44.1023, 9.8246],
        'Salerno' => [40.6824, 14.7681],
        'Amalfi' => [40.6340, 14.6027],
        'Positano' => [40.6281, 14.4850],
        'Capri' => [40.5508, 14.2430],
        'Sanremo' => [43.8159, 7.7761],
        'Portofino' => [44.3036, 9.2100],
        'Cortona' => [43.2747, 11.9875],
        'Assisi' => [43.0711, 12.6195],
        'Orvieto' => [42.7190, 12.1134],
        'Ravello' => [40.6519, 14.6128],
        'Montepulciano' => [43.0993, 11.7867],
        'San Gimignano' => [43.4681, 11.0435],
        'Alberobello' => [40.7863, 17.2360],
        'Matera' => [40.6663, 16.6043],
    ];

    private array $hotelNames = [
        'Grand Hotel',
        'Boutique Hotel',
        'Villa Resort',
        'Palace Hotel',
        'Hotel & Spa',
        'Historic Inn',
        'Seaside Resort',
        'Mountain Lodge',
        'City Center Hotel',
        'Garden Hotel',
        'Design Hotel',
        'Luxury Suites',
        'Riverside Hotel',
        'Château Hotel',
        'Vintage Hotel',
        'Spa & Wellness Resort',
        'View Hotel',
        'Relais & Châteaux',
        'Albergo Diffuso',
        'Agriturismo',
        'Casa Vacanze',
        'Hotel Residence',
        'Executive Hotel',
        'Family Resort',
        'Romantic Retreat',
        'Business Hotel',
        'Art Hotel',
        'Wine Hotel',
        'Beach Resort',
        'Lakefront Hotel',
    ];

    private array $descriptions = [
        'Elegant accommodation with stunning views and premium amenities in the heart of the city.',
        'Charming historic property with modern comforts and personalized service.',
        'Luxury retreat with spa, pool, and gourmet dining overlooking the landscape.',
        'Boutique hotel offering unique design and exceptional hospitality.',
        'Family-friendly resort with activities for all ages and direct beach access.',
        'Refined accommodation blending tradition with contemporary luxury.',
        'Romantic hideaway perfect for couples, with panoramic views.',
        'Central location steps from landmarks, shops, and restaurants.',
        'Peaceful escape surrounded by vineyards and rolling hills.',
        'Upscale property with rooftop terrace and fine dining.',
        'Restored historic building with original features and modern amenities.',
        'Seaside property with private beach and water sports.',
        'Cozy inn with fireplace lounges and mountain views.',
        'Urban retreat with rooftop pool and skyline views.',
        'Country estate with gardens, pool, and farm-to-table dining.',
    ];

    private array $addressParts = [
        'Via Roma', 'Piazza Garibaldi', 'Corso Vittorio Emanuele', 'Via Dante', 'Lungomare',
        'Via Mazzini', 'Piazza Duomo', 'Corso Italia', 'Via Nazionale', 'Largo Colombo',
        'Via Cavour', 'Piazza San Marco', 'Via Garibaldi', 'Corso Umberto', 'Lungarno',
    ];

    public function run(): void
    {
        $italy = Country::where('code', 'IT')->first();
        if (! $italy) {
            return;
        }

        $cities = City::where('country_id', $italy->id)->get();
        if ($cities->isEmpty()) {
            return;
        }

        $vendors = User::where('role', 'vendor')->get();
        if ($vendors->isEmpty()) {
            return;
        }

        $amenityIds = Amenity::pluck('id')->toArray();
        if (empty($amenityIds)) {
            return;
        }

        $target = 50;
        $vendorIds = $vendors->pluck('id')->toArray();

        for ($i = 0; $i < $target; $i++) {
            $city = $cities->get($i % $cities->count());
            $vendorId = $vendorIds[$i % count($vendorIds)];
            $coords = $this->cityCoordinates[$city->name] ?? [41.9, 12.5];

            $baseName = $this->hotelNames[$i % count($this->hotelNames)];
            $name = $baseName . ' ' . $city->name . ' ' . ($i + 1);
            $address = $this->addressParts[$i % count($this->addressParts)] . ' ' . (rand(1, 99) + $i);

            $hotel = Hotel::firstOrCreate(
                ['name' => $name],
                [
                    'vendor_id' => $vendorId,
                    'name' => $name,
                    'description' => $this->descriptions[$i % count($this->descriptions)],
                    'address' => $address,
                    'country_id' => $italy->id,
                    'city_id' => $city->id,
                    'city' => $city->name,
                    'country' => 'Italy',
                    'latitude' => $coords[0] + (rand(-100, 100) / 10000),
                    'longitude' => $coords[1] + (rand(-100, 100) / 10000),
                    'check_in' => ['14:00:00', '15:00:00', '16:00:00'][$i % 3],
                    'check_out' => ['10:00:00', '11:00:00', '12:00:00'][$i % 3],
                    'status' => 'active',
                    'tax_rate' => 0.10,
                    'tax_name' => 'IVA',
                    'tax_inclusive' => false,
                ]
            );

            $count = rand(4, min(10, count($amenityIds)));
            $shuffled = $amenityIds;
            shuffle($shuffled);
            $hotel->amenities()->sync(array_slice($shuffled, 0, $count));
        }
    }
}
