<?php

namespace App\Console\Commands;

use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadCityImages extends Command
{
    protected $signature = 'cities:download-images
                            {--country= : Only process cities from this country code (e.g. IT)}
                            {--dry-run : Show what would be done without downloading}
                            {--force : Re-download even if city already has an image}
                            {--source=wikipedia : Image source: wikipedia (default, no key) or unsplash (requires UNSPLASH_ACCESS_KEY)}
                            {--limit= : Limit number of cities to process (for testing)}';

    protected $description = 'Download city images from Wikipedia/Unsplash and link them to cities in the database';

    public function handle(): int
    {
        $countryCode = $this->option('country');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $source = $this->option('source');

        $query = City::query()->with('country');
        if ($countryCode) {
            $query->whereHas('country', fn ($q) => $q->where('code', $countryCode));
        }
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('image')->orWhere('image', '');
            });
        }
        if ($limit = (int) $this->option('limit')) {
            $query->limit($limit);
        }

        $cities = $query->get();
        if ($cities->isEmpty()) {
            $this->info('No cities to process. Use --force to re-download existing images.');
            return 0;
        }

        $this->info("Processing {$cities->count()} cities (source: {$source})");
        if ($dryRun) {
            $this->warn('DRY RUN - no files will be downloaded');
        }

        $storageDir = Storage::disk('public')->path('cities');
        if (!$dryRun) {
            File::ensureDirectoryExists($storageDir);
        }

        $success = 0;
        $failed = 0;

        foreach ($cities as $index => $city) {
            if ($index > 0 && !$dryRun) {
                usleep(500000); // 0.5s delay between requests to respect rate limits
            }
            $countryName = $city->country?->name ?? 'Italy';
            $searchTerms = ["{$city->name}, {$countryName}", $city->name];
            $imageUrl = null;

            if ($source === 'unsplash') {
                $imageUrl = $this->fetchFromUnsplash($city->name);
            } else {
                foreach ($searchTerms as $term) {
                    $imageUrl = $this->fetchFromWikipedia($term);
                    if ($imageUrl) {
                        break;
                    }
                }
            }

            if (!$imageUrl) {
                $this->line("  <fg=red>✗</> {$city->name} – no image found");
                $failed++;
                continue;
            }

            $slug = Str::slug($city->name);
            $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $ext = 'jpg';
            }
            $filename = "{$slug}.{$ext}";
            $storagePath = "cities/{$filename}";

            if (!$dryRun) {
                $downloaded = $this->downloadImage($imageUrl, $storageDir . '/' . $filename);
                if (!$downloaded) {
                    $this->line("  <fg=red>✗</> {$city->name} – download failed");
                    $failed++;
                    continue;
                }
                $city->update(['image' => $storagePath]);
            }

            $this->line("  <fg=green>✓</> {$city->name} ← " . ($dryRun ? $imageUrl : $storagePath));
            $success++;
        }

        $this->newLine();
        $this->info("Done: {$success} succeeded, {$failed} failed.");
        return 0;
    }

    /**
     * Fetch image URL from Wikipedia API (no API key required).
     */
    private function fetchFromWikipedia(string $searchTerm): ?string
    {
        $url = 'https://en.wikipedia.org/w/api.php?' . http_build_query([
            'action' => 'query',
            'titles' => $searchTerm,
            'prop' => 'pageimages',
            'format' => 'json',
            'pithumbsize' => 1200,
        ]);

        $response = Http::timeout(15)
            ->withHeaders(['User-Agent' => 'HotelBooking/1.0 (https://github.com/hotelbooking; contact@example.com)'])
            ->get($url);
        if (!$response->successful()) {
            return null;
        }

        $data = $response->json('query.pages');
        if (!$data) {
            return null;
        }

        $page = reset($data);
        if (isset($page['invalid']) || isset($page['missing'])) {
            return null;
        }

        return $page['thumbnail']['source'] ?? null;
    }

    /**
     * Fetch image URL from Unsplash (requires UNSPLASH_ACCESS_KEY in .env).
     */
    private function fetchFromUnsplash(string $cityName): ?string
    {
        $key = config('services.unsplash.key');
        if (!$key) {
            $this->warn('Unsplash requires UNSPLASH_ACCESS_KEY. Falling back to Wikipedia.');
            return $this->fetchFromWikipedia($cityName);
        }

        $url = 'https://api.unsplash.com/search/photos?' . http_build_query([
            'query' => $cityName . ' city skyline',
            'orientation' => 'landscape',
            'per_page' => 1,
        ]);

        $response = Http::withHeaders(['Authorization' => 'Client-ID ' . $key])
            ->timeout(15)
            ->get($url);

        if (!$response->successful()) {
            return null;
        }

        $results = $response->json('results');
        if (empty($results)) {
            return null;
        }

        return $results[0]['urls']['regular'] ?? $results[0]['urls']['full'] ?? null;
    }

    /**
     * Download image from URL to local path.
     */
    private function downloadImage(string $url, string $path): bool
    {
        try {
            $opts = [
                'http' => [
                    'timeout' => 30,
                    'user_agent' => 'HotelBooking/1.0 (Hotel booking; +https://example.com) PHP/' . PHP_VERSION,
                    'header' => "Accept: image/*\r\n",
                ],
                'ssl' => ['verify_peer' => true],
            ];
            $context = stream_context_create($opts);
            $content = @file_get_contents($url, false, $context);
            if ($content === false || strlen($content) < 500) {
                return false;
            }
            return File::put($path, $content) !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
