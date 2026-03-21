<?php

namespace App\Console\Commands;

use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LinkCityImages extends Command
{
    protected $signature = 'cities:link-images
                            {--source= : Path to city images folder (default: project_root/Image/city)}
                            {--dry-run : Show what would be done without making changes}
                            {--force : Overwrite existing city images}';

    protected $description = 'Link local city images from Image/city folder to cities in the database';

    /** Known filename variations that do not match city name exactly */
    private array $filenameToCity = [
        'alberobell' => 'Alberobello',
        'roof-of-milan-cathedral-duomo-di-milano-with-gothi' => 'Milan',
        'venice-italy-view-free-photo' => 'Venice',
        'chioggia-italy-on-the-vena-canal-in-the-background' => null, // not in our cities
        'cefal_C3_B9-sicilia-isola-italia-estate-panorama-c' => null, // not in our cities
    ];

    public function handle(): int
    {
        $sourceDir = rtrim($this->option('source') ?? base_path('../Image/city'), '/');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if (!is_dir($sourceDir)) {
            $this->error("Folder not found: {$sourceDir}");

            return 1;
        }

        $files = $this->getImageFiles($sourceDir);
        if (empty($files)) {
            $this->error('No image files found in ' . $sourceDir);

            return 1;
        }

        $this->info('Found ' . count($files) . ' images in ' . $sourceDir);
        if ($dryRun) {
            $this->warn('DRY RUN – no changes will be made');
        }

        $storageDir = Storage::disk('public')->path('cities');
        if (!$dryRun) {
            File::ensureDirectoryExists($storageDir);
        }

        $success = 0;
        $skipped = 0;
        $citiesByName = City::all()->keyBy(fn ($c) => Str::lower($c->name));

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $baseName = pathinfo($filename, PATHINFO_FILENAME);
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            $cityName = $this->resolveCityName($baseName);
            if (!$cityName) {
                $this->line("  <fg=yellow>○</> {$filename} – no matching city, skipped");
                $skipped++;
                continue;
            }

            $city = $citiesByName->get(Str::lower($cityName));
            if (!$city) {
                $this->line("  <fg=yellow>○</> {$filename} → {$cityName} – city not in database, skipped");
                $skipped++;
                continue;
            }

            if (!$force && $city->image) {
                $this->line("  <fg=yellow>○</> {$city->name} – already has image (use --force to overwrite)");
                $skipped++;
                continue;
            }

            $slug = Str::slug($city->name);
            $newFilename = "{$slug}.{$ext}";
            $storagePath = "cities/{$newFilename}";

            if (!$dryRun) {
                $destPath = $storageDir . '/' . $newFilename;
                File::copy($filePath, $destPath);
                $city->update(['image' => $storagePath]);
            }

            $this->line("  <fg=green>✓</> {$city->name} ← " . ($dryRun ? $filename : $storagePath));
            $success++;
        }

        $this->newLine();
        $this->info("Done: {$success} linked, {$skipped} skipped.");
        return 0;
    }

    private function resolveCityName(string $baseName): ?string
    {
        if (array_key_exists($baseName, $this->filenameToCity)) {
            return $this->filenameToCity[$baseName];
        }
        if (array_key_exists(Str::lower($baseName), $this->filenameToCity)) {
            return $this->filenameToCity[Str::lower($baseName)];
        }

        return $baseName;
    }

    private function getImageFiles(string $dir): array
    {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = [];

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_file($path)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $files[] = $path;
                }
            }
        }

        sort($files);

        return $files;
    }
}
