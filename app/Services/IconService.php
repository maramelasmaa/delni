<?php

namespace App\Services;

use App\Models\Icon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class IconService
{
    private const DOWNLOAD_TIMEOUT = 10;

    private const DOWNLOAD_RETRY = 3;

    private const MAX_FILE_SIZE = 500 * 1024;

    public function downloadAndStore(string $url, string $name, string $color = '#F1620F'): Icon
    {
        try {
            $response = Http::timeout(self::DOWNLOAD_TIMEOUT)
                ->retry(self::DOWNLOAD_RETRY, 100)
                ->get($url);

            $response->throw();

            $content = $response->body();

            if (strlen($content) > self::MAX_FILE_SIZE) {
                throw new InvalidArgumentException('File size exceeds maximum allowed.');
            }

            $format = $this->validateFormat($content);
            $content = $format === 'svg' ? $this->colorizeSvg($content, $color) : $content;

            $slug = Str::slug($name);
            $fileName = "{$slug}.{$format}";

            Storage::disk('icons')->put($fileName, $content);

            return Icon::create([
                'name' => $name,
                'slug' => $slug,
                'file_path' => $fileName,
                'format' => $format,
                'color' => $color,
                'uploaded_by' => auth()->id(),
            ]);
        } catch (Throwable $e) {
            \Log::error('Icon download failed', [
                'message' => $e->getMessage(),
                'url' => $url,
            ]);
            throw $e;
        }
    }

    private function validateFormat(string $content): string
    {
        if (str_contains($content, '<?xml') || str_contains($content, '<svg')) {
            return 'svg';
        }

        if (str_starts_with($content, "\x89PNG")) {
            return 'png';
        }

        throw new InvalidArgumentException('Invalid icon format. Must be SVG or PNG.');
    }

    private function colorizeSvg(string $svg, string $color): string
    {
        return preg_replace(
            '/fill=["\']#[0-9A-F]{6}["\']|fill=["\']currentColor["\']/i',
            "fill=\"{$color}\"",
            $svg
        );
    }
}
