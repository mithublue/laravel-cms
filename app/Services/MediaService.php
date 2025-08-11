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
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', $baseName) ?: 'file';
        $filename = $safeBase.'-'.uniqid().'.'.$extension;

        // Store file
        $path = $file->storeAs($directory, $filename, $disk);
        $absolutePath = Storage::disk($disk)->path($path);

        // Gather metadata
        $size = filesize($absolutePath) ?: null;
        $mime = $file->getMimeType();
        $width = null; $height = null;
        if (in_array($mime, ['image/jpeg','image/png','image/gif','image/webp','image/bmp','image/svg+xml']) || str_starts_with((string) $mime, 'image/')) {
            try {
                $info = @getimagesize($absolutePath);
                if ($info) { $width = $info[0] ?? null; $height = $info[1] ?? null; }
            } catch (\Throwable $e) {
                // ignore
            }
        }
        $hash = null;
        try { $hash = hash_file('sha256', $absolutePath); } catch (\Throwable $e) { /* ignore */ }

        // Persist media record
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
