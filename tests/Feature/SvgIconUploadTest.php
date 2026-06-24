<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Icon;
use App\Services\SvgIconService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End-to-end proof of the Filament icon upload pipeline:
 * upload → colorize → store on the `icons` disk → DB row → served by /icon/{id}.
 */
class SvgIconUploadTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_uploaded_svg_is_stored_recorded_and_served(): void
    {
        Storage::fake('icons');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#000000"><path d="M4 4h16v16H4z"/></svg>';
        $file = UploadedFile::fake()->createWithContent('my-icon.svg', $svg);

        $icon = app(SvgIconService::class)->uploadAndColorize($file, 'Design Category Icon');

        // 1. DB row created correctly
        $this->assertSame('svg', $icon->format);
        $this->assertNotEmpty($icon->file_path);

        // 2. File physically stored on the icons disk
        Storage::disk('icons')->assertExists($icon->file_path);

        // 3. Colorized to currentColor so the app can tint it
        $stored = Storage::disk('icons')->get($icon->file_path);
        $this->assertStringContainsString('currentColor', $stored);

        // 4. Served over HTTP as a real SVG (the bug that was 500ing)
        $this->get("/icon/{$icon->id}")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');
    }
}
