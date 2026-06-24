<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class FaviconController
{
    public function __invoke(): Response
    {
        return response()->file(public_path('images/photo_2026-06-22_23-21-55.jpg'), [
            'Content-Type' => 'image/jpeg',
        ]);
    }
}
