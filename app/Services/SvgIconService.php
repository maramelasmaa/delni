<?php

namespace App\Services;

use App\Models\Icon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SvgIconService
{
    private const ICON_SIZE = 24;

    private const ICON_COLOR = 'currentColor';

    public function uploadAndColorize(UploadedFile $file, string $name, ?string $color = null): Icon
    {
        $content = $file->get();
        $color ??= self::ICON_COLOR;

        $content = $this->normalizeRootSvgTag($content);

        $content = $this->colorizeToColor($content, $color);

        // Make name unique if it already exists
        $baseName = $name;
        $counter = 1;
        while (Icon::where('name', $name)->exists()) {
            $name = "{$baseName} ({$counter})";
            $counter++;
        }

        // Save file
        $slug = Str::slug($name);
        $fileName = "{$slug}.svg";
        Storage::disk('icons')->put($fileName, $content);

        // Create record
        return Icon::create([
            'name' => $name,
            'slug' => $slug,
            'file_path' => $fileName,
            'format' => 'svg',
            'color' => $color,
            'uploaded_by' => auth()->id(),
        ]);
    }

    private function colorizeToColor(string $svg, string $color): string
    {
        $svg = preg_replace(
            '/fill=["\']#[0-9A-Fa-f]{6}["\']|fill=["\']currentColor["\']/i',
            'fill="'.$color.'"',
            $svg
        );

        $svg = preg_replace(
            '/stroke=["\']#[0-9A-Fa-f]{6}["\']|stroke=["\']currentColor["\']/i',
            'stroke="'.$color.'"',
            $svg
        );

        if (! str_contains($svg, 'fill="'.$color.'"') &&
            ! str_contains($svg, 'stroke="'.$color.'"')) {
            $svg = str_replace('<svg', '<svg fill="'.$color.'"', $svg);
        }

        return $svg;
    }

    private function normalizeRootSvgTag(string $svg): string
    {
        return preg_replace_callback('/<svg\b[^>]*>/i', function (array $matches): string {
            $tag = $matches[0];

            if (! preg_match('/\bviewBox\s*=/i', $tag)) {
                $tag = preg_replace('/<svg\b/i', '<svg viewBox="0 0 24 24"', $tag, 1);
            }

            if (preg_match('/\bwidth\s*=/i', $tag)) {
                $tag = preg_replace('/\bwidth\s*=\s*["\'][^"\']*["\']/i', 'width="'.self::ICON_SIZE.'"', $tag, 1);
            } else {
                $tag = preg_replace('/<svg\b/i', '<svg width="'.self::ICON_SIZE.'"', $tag, 1);
            }

            if (preg_match('/\bheight\s*=/i', $tag)) {
                $tag = preg_replace('/\bheight\s*=\s*["\'][^"\']*["\']/i', 'height="'.self::ICON_SIZE.'"', $tag, 1);
            } else {
                $tag = preg_replace('/<svg\b/i', '<svg height="'.self::ICON_SIZE.'"', $tag, 1);
            }

            return $tag;
        }, $svg, 1) ?? $svg;
    }
}
