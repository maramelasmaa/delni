<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Icon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IconControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_serves_an_uploaded_svg_icon(): void
    {
        Storage::fake('icons');
        Storage::disk('icons')->put('sample.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');

        $icon = Icon::create([
            'name' => 'Sample',
            'slug' => 'sample',
            'file_path' => 'sample.svg',
            'format' => 'svg',
            'uploaded_by' => null,
        ]);

        $this->get("/icon/{$icon->id}")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_it_404s_when_the_icon_file_is_missing(): void
    {
        Storage::fake('icons');

        $icon = Icon::create([
            'name' => 'Gone',
            'slug' => 'gone',
            'file_path' => 'missing.svg',
            'format' => 'svg',
            'uploaded_by' => null,
        ]);

        $this->get("/icon/{$icon->id}")->assertNotFound();
    }

    public function test_it_404s_when_file_path_is_blank(): void
    {
        Storage::fake('icons');

        $icon = Icon::create([
            'name' => 'Blank',
            'slug' => 'blank',
            'file_path' => '',
            'format' => 'svg',
            'uploaded_by' => null,
        ]);

        $this->get("/icon/{$icon->id}")->assertNotFound();
    }
}
