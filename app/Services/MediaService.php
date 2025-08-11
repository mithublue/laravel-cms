<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    public static function storeFromUpload(UploadedFile $file, ?int $authorId = null, ?string $directory = null): Media
    {
        $disk = 'public';
        $directory = trim($directory ?: 'uploads/'.date('Y/m'), '/');

        // Compute hash from the temporary uploaded file BEFORE storing
        $tmpPath = $file->getRealPath();
        $hash = null;
        try { $hash = $tmpPath ? hash_file('sha256', $tmpPath) : null; } catch (\Throwable $e) { /* ignore */ }

        // If a file with the same hash already exists, just return it (avoid duplicate insert)
        if ($hash) {
            $existing = Media::where('hash', $hash)->first();
            if ($existing) {
                return $existing;
            }
        }

        // Prepare file metadata
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', $baseName) ?: 'file';
        $filename = $safeBase.'-'.uniqid().'.'.$extension;

        // Gather metadata from temp file (faster/one IO) and fall back to UploadedFile methods
        $size = null;
        try { $size = $file->getSize(); } catch (\Throwable $e) { /* ignore */ }
        $mime = $file->getMimeType();
        $width = null; $height = null;
        if ($mime && (in_array($mime, ['image/jpeg','image/png','image/gif','image/webp','image/bmp','image/svg+xml']) || str_starts_with((string) $mime, 'image/'))) {
            try {
                $info = $tmpPath ? @getimagesize($tmpPath) : null;
                if ($info) { $width = $info[0] ?? null; $height = $info[1] ?? null; }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Store file
        $path = $file->storeAs($directory, $filename, $disk);
        $absolutePath = Storage::disk($disk)->path($path);

        // If hash wasn't computed earlier for some reason, compute now from stored file
        if (!$hash) {
            try { $hash = hash_file('sha256', $absolutePath); } catch (\Throwable $e) { /* ignore */ }
        }

        return Media::create([
            'author_id' => $authorId,
            'disk' => $disk,
            'directory' => $directory,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'extension' => $extension,
            'mime_type' => $mime,
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'hash' => $hash,
        ]);
    }
}
