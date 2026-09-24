<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Media
{
    public static function store(UploadedFile $file, string $folder): string
    {
        $path = $file->store($folder, 'public');

        static::mirror($path);

        return $path;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
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

        if (is_file(public_path('uploads/'.$path))) {
            return asset('uploads/'.$path);
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
