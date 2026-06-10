<?php

namespace App\Services;

use App\Models\Icon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SvgIconService
{
    private const ICON_SIZE = '24';

    private const ICON_COLOR = '#F1620F';

    public function uploadAndColorize(UploadedFile $file, string $name): Icon
    {
        $content = $file->get();

        // Add viewBox if missing
        if (! str_contains($content, 'viewBox')) {
            $content = str_replace('<svg', '<svg viewBox="0 0 24 24"', $content);
        }

        // Set fixed size
        $content = preg_replace('/width="[^"]*"/', 'width="'.self::ICON_SIZE.'"', $content);
        $content = preg_replace('/height="[^"]*"/', 'height="'.self::ICON_SIZE.'"', $content);

        // Colorize to orange
        $content = $this->colorizeToOrange($content);

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
            'color' => self::ICON_COLOR,
            'uploaded_by' => auth()->id(),
        ]);
    }

    private function colorizeToOrange(string $svg): string
    {
        // Replace all fill colors with orange
        $svg = preg_replace(
            '/fill=["\']#[0-9A-Fa-f]{6}["\']|fill=["\']currentColor["\']/i',
            'fill="'.self::ICON_COLOR.'"',
            $svg
        );

        // Replace all stroke colors with orange
        $svg = preg_replace(
            '/stroke=["\']#[0-9A-Fa-f]{6}["\']|stroke=["\']currentColor["\']/i',
            'stroke="'.self::ICON_COLOR.'"',
            $svg
        );

        // If no fill/stroke found, add orange fill
        if (! str_contains($svg, 'fill="'.self::ICON_COLOR.'"') &&
            ! str_contains($svg, 'stroke="'.self::ICON_COLOR.'"')) {
            $svg = str_replace('<svg', '<svg fill="'.self::ICON_COLOR.'"', $svg);
        }

        return $svg;
    }
}
