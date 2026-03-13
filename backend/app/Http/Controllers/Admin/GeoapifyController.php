<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeoapifyController extends Controller
{
    /**
     * Proxy Geoapify Address Autocomplete API.
     * Keeps API key server-side and returns address suggestions.
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
                'error' => 'Geoapify API key not configured. Add GEOAPIFY_API_KEY to .env',
            ], 500);
        }

        $response = Http::get(config('geoapify.autocomplete_url'), [
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

        // Normalize: Geoapify format=json returns "results" with props at top level;
        // format=geojson returns "features" with props in feature.properties
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
