<?php

namespace App\Services;

use App\Models\VendorProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorBusinessDocumentService
{
    public const MAX_FILES = 25;

    public const MAX_FILE_KB = 10240;

    /**
     * @return array<int, array{id: string, path: string, original_name: string, size: int, mime: string|null, uploaded_at: string}>
     */
    public function list(VendorProfile $profile): array
    {
        $raw = $profile->documents;
        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter($raw, fn ($d) => is_array($d) && ! empty($d['path']) && ! empty($d['id'])));
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function append(VendorProfile $profile, array $files): void
    {
        $current = $this->list($profile);
        $disk = Storage::disk('public');
        $userId = $profile->user_id;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
            $safeExt = preg_match('/^[a-z0-9]{1,10}$/i', $ext) ? strtolower($ext) : 'bin';
            $filename = Str::uuid()->toString().'.'.$safeExt;
            $dir = 'vendor-documents/'.$userId;
            $path = $file->storeAs($dir, $filename, 'public');

            $current[] = [
                'id' => (string) Str::uuid(),
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'uploaded_at' => now()->toIso8601String(),
            ];
        }

        $profile->update(['documents' => $current]);
    }

    public function deleteById(VendorProfile $profile, string $documentId): bool
    {
        $current = $this->list($profile);
        $found = null;
        foreach ($current as $i => $doc) {
            if (($doc['id'] ?? '') === $documentId) {
                $found = $i;
                break;
            }
        }
        if ($found === null) {
            return false;
        }
        $removed = $current[$found];
        unset($current[$found]);
        $current = array_values($current);

        if (! empty($removed['path'])) {
            Storage::disk('public')->delete($removed['path']);
        }

        $profile->update(['documents' => $current]);

        return true;
    }

    public function findById(VendorProfile $profile, string $documentId): ?array
    {
        foreach ($this->list($profile) as $doc) {
            if (($doc['id'] ?? '') === $documentId) {
                return $doc;
            }
        }

        return null;
    }

    public function absolutePath(string $relativePath): ?string
    {
        if (! str_starts_with($relativePath, 'vendor-documents/')) {
            return null;
        }

        $full = Storage::disk('public')->path($relativePath);

        return is_file($full) ? $full : null;
    }
}
