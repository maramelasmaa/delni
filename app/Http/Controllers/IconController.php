<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Icon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IconController
{
    public function __invoke(Icon $icon): BinaryFileResponse
    {
        $disk = Storage::disk('icons');

        // A null/empty file_path would make $disk->missing() throw (TypeError) → 500.
        // Treat missing-or-blank as a clean 404 so clients fall back gracefully.
        if (blank($icon->file_path) || $disk->missing($icon->file_path)) {
            abort(404);
        }

        $path = $disk->path($icon->file_path);
        $mimeType = $icon->format === 'svg' ? 'image/svg+xml' : "image/{$icon->format}";

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
