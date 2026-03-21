<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LinkImagesToHotelsAndRooms extends Command
{
    protected $signature = 'images:link
                            {--source= : Path to Image folder (default: project_root/Image)}
                            {--dry-run : Show what would be done without making changes}
                            {--fresh : Delete existing hotel/room images before linking}';

    protected $description = 'Rename images to 1, 2, 3... copy to storage, and link with hotels and rooms';

    public function handle(): int
    {
        $sourceDir = rtrim($this->option('source') ?? base_path('../Image'), '/');
        $dryRun = $this->option('dry-run');
        $fresh = $this->option('fresh');

        if (! is_dir($sourceDir)) {
            $this->error("Image folder not found: {$sourceDir}");

            return 1;
        }

        $files = $this->getImageFiles($sourceDir);
        if (empty($files)) {
            $this->error('No image files found in ' . $sourceDir);

            return 1;
        }

        $this->info('Found ' . count($files) . ' images');
        $hotels = Hotel::orderBy('id')->get();
        $rooms = Room::with('hotel')->orderBy('hotel_id')->orderBy('id')->get();

        if ($hotels->isEmpty()) {
            $this->error('No hotels found in database. Run HotelSeeder first.');

            return 1;
        }

        // Split: ~70% for hotels, ~30% for rooms
        $hotelCount = (int) ceil(count($files) * 0.7);
        $roomCount = count($files) - $hotelCount;

        $hotelFiles = array_slice($files, 0, $hotelCount);
        $roomFiles = array_slice($files, $hotelCount, $roomCount);

        $this->info("Assigning {$hotelCount} images to hotels, {$roomCount} to rooms");

        if ($dryRun) {
            $this->warn('DRY RUN - no changes will be made');
        }

        if ($fresh && ! $dryRun) {
            $this->info('Clearing existing hotel and room images...');
            foreach (HotelImage::all() as $img) {
                if (Storage::disk('public')->exists($img->image_path)) {
                    Storage::disk('public')->delete($img->image_path);
                }
                $img->delete();
            }
            foreach (RoomImage::all() as $img) {
                if (Storage::disk('public')->exists($img->image_path)) {
                    Storage::disk('public')->delete($img->image_path);
                }
                $img->delete();
            }
        }

        if (! $dryRun && ! File::exists(Storage::disk('public')->path('.'))) {
            $this->warn('Storage link may not exist. Run: php artisan storage:link');
        }

        $num = 1;

        foreach ($hotelFiles as $index => $filePath) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $newName = $num . '.' . $ext;
            $hotel = $hotels->get($index % $hotels->count());

            if (! $dryRun) {
                $storagePath = 'hotels/' . $hotel->id . '/' . $newName;
                $fullPath = Storage::disk('public')->path($storagePath);
                File::ensureDirectoryExists(dirname($fullPath));
                File::copy($filePath, $fullPath);

                HotelImage::create([
                    'hotel_id' => $hotel->id,
                    'image_path' => $storagePath,
                    'alt_text' => $hotel->name . ' - Image ' . $num,
                    'is_banner' => $index === 0 || $hotel->images()->count() === 0,
                    'sort_order' => $hotel->images()->max('sort_order') + 1,
                ]);
            }

            $this->line("  [{$num}] {$hotel->name} ← " . basename($filePath));
            $num++;
        }

        foreach ($roomFiles as $index => $filePath) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $newName = $num . '.' . $ext;
            $room = $rooms->get($index % $rooms->count());

            if (! $dryRun) {
                $storagePath = 'rooms/' . $room->id . '/' . $newName;
                $fullPath = Storage::disk('public')->path($storagePath);
                File::ensureDirectoryExists(dirname($fullPath));
                File::copy($filePath, $fullPath);

                RoomImage::create([
                    'room_id' => $room->id,
                    'image_path' => $storagePath,
                    'alt_text' => $room->name . ' - Image ' . $num,
                    'is_banner' => $room->images()->count() === 0,
                    'sort_order' => $room->images()->count(),
                ]);
            }

            $this->line("  [{$num}] Room: {$room->name} ({$room->hotel->name}) ← " . basename($filePath));
            $num++;
        }

        if (! $dryRun) {
            // Rename original files in Image folder to 1.ext, 2.ext, etc.
            // Two-phase: first to temp names to avoid overwriting, then to final names
            $this->info('Renaming files in source Image folder...');
            $sourceDir = dirname($files[0]);

            foreach ($files as $i => $filePath) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $tempPath = $sourceDir . '/__' . ($i + 1) . '__.' . $ext;
                if (file_exists($filePath)) {
                    rename($filePath, $tempPath);
                }
            }

            foreach ($files as $i => $oldPath) {
                $ext = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));
                $tempPath = $sourceDir . '/__' . ($i + 1) . '__.' . $ext;
                $newPath = $sourceDir . '/' . ($i + 1) . '.' . $ext;
                if (file_exists($tempPath)) {
                    rename($tempPath, $newPath);
                }
            }
        }

        $this->info('Done!');

        return 0;
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
