<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodeController extends Controller
{
    /**
     * Proxy Geoapify Address Autocomplete for customer map search.
     * Throttled to prevent abuse.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $text = trim((string) $request->query('text', ''));
        if (strlen($text) < 2) {
            return response()->json(['features' => []]);
        }

        $apiKey = config('geoapify.api_key');
        if (empty($apiKey)) {
            return response()->json([
                'error' => 'Geocoding not configured',
                'features' => [],
            ], 503);
        }

        $response = Http::timeout(5)->get(config('geoapify.autocomplete_url'), [
            'text' => $text,
            'format' => 'json',
            'apiKey' => $apiKey,
            'limit' => 10,
        ]);

        if (! $response->successful()) {
            return response()->json([
                'error' => 'Geocoding service unavailable',
                'features' => [],
            ], 502);
        }

        $data = $response->json();
        $raw = $data['results'] ?? $data['features'] ?? [];

        $features = [];
        foreach ($raw as $item) {
            if (isset($item['properties'])) {
                $features[] = $item;
            } else {
                $features[] = [
                    'type' => 'Feature',
                    'properties' => [
                        'lat' => $item['lat'] ?? null,
                        'lon' => $item['lon'] ?? null,
                        'city' => $item['city'] ?? null,
                        'country' => $item['country'] ?? null,
                        'formatted' => $item['formatted'] ?? null,
                        'address_line1' => $item['address_line1'] ?? null,
                        'address_line2' => $item['address_line2'] ?? null,
                        'county' => $item['county'] ?? null,
                        'state' => $item['state'] ?? null,
                    ],
                ];
            }
        }

        return response()->json(['features' => $features]);
    }
}
