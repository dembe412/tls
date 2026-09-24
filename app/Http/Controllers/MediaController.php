<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function __invoke(string $path): BinaryFileResponse
    {
        abort_unless(Storage::disk('public')->exists($path), 404);

        $absolute = Storage::disk('public')->path($path);

        abort_unless(is_file($absolute), 404);

        return response()->file($absolute, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
