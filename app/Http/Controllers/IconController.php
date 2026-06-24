<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Icon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class IconController
{
    public function __invoke(Icon $icon): Response
    {
        $path = Storage::disk('icons')->path($icon->file_path);
        $mimeType = $icon->format === 'svg' ? 'image/svg+xml' : "image/{$icon->format}";

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
        ]);
    }
}
