<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class ProfileImageService
{
    private const DISK = 'public';

    private const AVATAR_MAX_UPLOAD_SIZE = 2_097_152; // 2MB

    private const COVER_MAX_UPLOAD_SIZE = 4_194_304; // 4MB

    private const PORTFOLIO_MAX_UPLOAD_SIZE = 4_194_304; // 4MB

    // Mobile-optimized dimensions (enforced on upload)
    private const AVATAR_WIDTH = 240;

    private const AVATAR_HEIGHT = 240;

    private const COVER_WIDTH = 1080;

    private const COVER_HEIGHT = 540; // 2:1 aspect ratio

    private const PORTFOLIO_WIDTH = 1080;

    private const PORTFOLIO_HEIGHT = 1080;

    private readonly ImageManager $manager;

    public function __construct()
    {
        $this->manager = ImageManager::usingDriver(Driver::class);
    }

    public function storeAvatar(UploadedFile $file): string
    {
        $this->validateImage($file);
        $this->validateSize($file, self::AVATAR_MAX_UPLOAD_SIZE, '2 ميجابايت');

        $image = $this->manager->decodePath($file->getRealPath());
        // Cover to exact 240x240 (2x retina for 120x120 display)
        $image->cover(self::AVATAR_WIDTH, self::AVATAR_HEIGHT);

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: $this->calculateQuality($image));

        $path = 'profiles/avatars/'.Str::uuid().'.webp';
        Storage::disk(self::DISK)->put($path, (string) $encoded);

        return $path;
    }

    public function storeGalleryImage(UploadedFile $file): string
    {
        $this->validateImage($file);
        $this->validateSize($file, self::COVER_MAX_UPLOAD_SIZE, '4 ميجابايت');

        $image = $this->manager->decodePath($file->getRealPath());
        // Cover to exact 1080x540 (2:1 aspect ratio, mobile-optimized)
        $image->cover(self::COVER_WIDTH, self::COVER_HEIGHT);

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: $this->calculateQuality($image));

        $path = 'profiles/covers/'.Str::uuid().'.webp';
        Storage::disk(self::DISK)->put($path, (string) $encoded);

        return $path;
    }

    public function storePortfolioImage(UploadedFile $file): string
    {
        $this->validateImage($file);
        $this->validateSize($file, self::PORTFOLIO_MAX_UPLOAD_SIZE, '4 ميجابايت');

        $image = $this->manager->decodePath($file->getRealPath());
        // Cover to exact 1080x1080 (square format for gallery)
        $image->cover(self::PORTFOLIO_WIDTH, self::PORTFOLIO_HEIGHT);

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: $this->calculateQuality($image));

        $path = 'portfolio/images/'.Str::uuid().'.webp';
        Storage::disk(self::DISK)->put($path, (string) $encoded);

        return $path;
    }

    public function replaceImage(?string $oldPath, UploadedFile $file, string $type = 'avatar'): string
    {
        $newPath = match ($type) {
            'avatar' => $this->storeAvatar($file),
            'cover' => $this->storeGalleryImage($file),
            'portfolio' => $this->storePortfolioImage($file),
            default => throw new \InvalidArgumentException("Invalid image type: {$type}"),
        };

        if ($oldPath !== null && $oldPath !== $newPath) {
            $this->deleteImage($oldPath);
        }

        return $newPath;
    }

    public function deleteImage(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    private function validateImage(UploadedFile $file): void
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (! in_array($file->getMimeType(), $allowedMimeTypes, true)) {
            throw ValidationException::withMessages([
                'file' => 'الصورة يجب أن تكون JPEG أو PNG أو WebP.',
            ]);
        }

        if (! @getimagesize($file->getRealPath())) {
            throw ValidationException::withMessages([
                'file' => 'الصورة غير صالحة أو تالفة.',
            ]);
        }
    }

    private function validateSize(UploadedFile $file, int $maxBytes, string $label): void
    {
        if ($file->getSize() > $maxBytes) {
            throw ValidationException::withMessages([
                'file' => "حجم الصورة كبير جداً. الحد الأقصى {$label}.",
            ]);
        }
    }

    private function scaleToMax(Image $image, int $maxDimension): void
    {
        $width = $image->width();
        $height = $image->height();

        if ($width <= $maxDimension && $height <= $maxDimension) {
            return;
        }

        if ($width > $height) {
            $image->scale(width: $maxDimension);

            return;
        }

        $image->scale(height: $maxDimension);
    }

    private function calculateQuality(Image $image): int
    {
        $dimensions = $image->width() * $image->height();

        if ($dimensions > 2_560_000) {
            return 60;
        }

        if ($dimensions > 640_000) {
            return 70;
        }

        return 75;
    }
}
