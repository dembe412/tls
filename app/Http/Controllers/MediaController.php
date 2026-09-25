<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function __invoke(Request $request, string $path): Response|BinaryFileResponse
    {
        abort_unless($this->isSafePath($path), 404);

        // 1. Check database first (persistent storage across serverless lambdas)
        try {
            $media = DB::table('media_files')->where('path', $path)->first();
            if ($media) {
                $etag = '"'.md5($media->content).'"';
                if ($request->headers->get('If-None-Match') === $etag) {
                    return response('', 304, [
                        'ETag' => $etag,
                        'Cache-Control' => 'public, max-age=31536000, immutable',
                    ]);
                }

                return response(base64_decode($media->content), 200, [
                    'Content-Type' => $media->mime_type,
                    'Content-Length' => (string) $media->size,
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                    'ETag' => $etag,
                ]);
            }
        } catch (\Throwable) {
            // Ignore DB error and continue to local fallback
        }

        // 2. Check local disk fallback (e.g. seeded files or local development)
        try {
            $disk = Storage::disk('public');
            if ($disk->exists($path)) {
                $absolute = realpath($disk->path($path));
                $root = realpath($disk->path(''));

                if ($absolute && $root && is_file($absolute) && str_starts_with($absolute, $root.DIRECTORY_SEPARATOR)) {
                    return response()->file($absolute, [
                        'Cache-Control' => 'public, max-age=604800',
                    ]);
                }
            }
        } catch (\Throwable) {
            // Read-only filesystem or missing disk
        }

        // 3. Check public_path('uploads/'.$path)
        try {
            $upload = public_path('uploads/'.$path);
            if (is_file($upload)) {
                return response()->file($upload, [
                    'Cache-Control' => 'public, max-age=604800',
                ]);
            }
        } catch (\Throwable) {
            // Read-only filesystem
        }

        abort(404);
    }

    private function isSafePath(string $path): bool
    {
        return $path !== ''
            && ! str_contains($path, "\0")
            && ! str_contains($path, '\\')
            && ! str_contains($path, ':')
            && ! preg_match('#(^|/)\.\.?(/|$)#', $path);
    }
}
