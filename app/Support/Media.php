<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media
{
    public static function store(UploadedFile $file, string $folder): string
    {
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin';
        $filename = Str::random(40).'.'.$extension;
        $path = trim($folder, '/').'/'.$filename;
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $size = $file->getSize() ?: 0;
        $content = base64_encode($file->get());

        // Save to database for persistent storage across Vercel serverless lambdas
        try {
            DB::table('media_files')->updateOrInsert(
                ['path' => $path],
                [
                    'mime_type' => $mime,
                    'size' => $size,
                    'content' => $content,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            report($e);
        }

        // Also save to public disk if disk is writable (e.g. testing / local dev)
        try {
            Storage::disk('public')->put($path, $file->get());
            static::mirror($path);
        } catch (\Throwable) {
            // Read-only filesystem on Vercel
        }

        return $path;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            DB::table('media_files')->where('path', $path)->delete();
        } catch (\Throwable) {
            // Ignore DB deletion errors
        }

        try {
            Storage::disk('public')->delete($path);
        } catch (\Throwable) {
            // Ignore storage deletion errors
        }

        try {
            $upload = public_path('uploads/'.$path);
            if (is_file($upload)) {
                File::delete($upload);
            }
        } catch (\Throwable) {
            // Read-only filesystem (e.g. Vercel)
        }
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return url('media/'.$path);
    }

    public static function mirror(string $path): void
    {
        try {
            $disk = Storage::disk('public');

            if (! $disk->exists($path)) {
                return;
            }

            $source = $disk->path($path);

            if (! is_file($source)) {
                return;
            }

            $target = public_path('uploads/'.$path);
            File::ensureDirectoryExists(dirname($target));
            File::copy($source, $target);
        } catch (\Throwable) {
            // In serverless environments like Vercel, public_path is read-only.
            // Silently skip mirroring so the upload succeeds and MediaController serves it.
        }
    }
}
