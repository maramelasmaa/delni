<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
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

    private const PORTFOLIO_WIDTH = 900;

    private const PORTFOLIO_HEIGHT = 900;

    // WebP output quality per type (0-100). Lower = smaller files. Tuned aggressively
    // for mobile delivery; raise a single value if that image type ever looks too soft.
    private const AVATAR_QUALITY = 65;

    private const COVER_QUALITY = 55;

    private const PORTFOLIO_QUALITY = 55;

    private readonly ImageManager $manager;

    public function __construct()
    {
        $this->manager = ImageManager::usingDriver(Driver::class);
    }

    public function storeAvatar(UploadedFile $file): string
    {
        $this->validateImage($file);
        $this->validateSize($file, self::AVATAR_MAX_UPLOAD_SIZE, '2 ميجابايت');

        // ->orient() bakes in EXIF rotation (phone portrait shots) and strips metadata.
        $image = $this->manager->decodePath($file->getRealPath())->orient();
        // Cover to exact 240x240 (2x retina for 120x120 display)
        $image->cover(self::AVATAR_WIDTH, self::AVATAR_HEIGHT);

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: self::AVATAR_QUALITY);

        $path = 'profiles/avatars/'.Str::uuid().'.webp';
        Storage::disk(self::DISK)->put($path, (string) $encoded);

        return $path;
    }

    public function storeGalleryImage(UploadedFile $file): string
    {
        $this->validateImage($file);
        $this->validateSize($file, self::COVER_MAX_UPLOAD_SIZE, '4 ميجابايت');

        $image = $this->manager->decodePath($file->getRealPath())->orient();
        // Cover to exact 1080x540 (2:1 aspect ratio, mobile-optimized)
        $image->cover(self::COVER_WIDTH, self::COVER_HEIGHT);

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: self::COVER_QUALITY);

        $path = 'profiles/covers/'.Str::uuid().'.webp';
        Storage::disk(self::DISK)->put($path, (string) $encoded);

        return $path;
    }

    public function storePortfolioImage(UploadedFile $file): string
    {
        $this->validateImage($file);
        $this->validateSize($file, self::PORTFOLIO_MAX_UPLOAD_SIZE, '4 ميجابايت');

        $image = $this->manager->decodePath($file->getRealPath())->orient();
        // Cover to exact 900x900 (square format for gallery)
        $image->cover(self::PORTFOLIO_WIDTH, self::PORTFOLIO_HEIGHT);

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: self::PORTFOLIO_QUALITY);

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

    /**
     * Re-encode an already-stored image in place using the current dimension/quality
     * settings. Only overwrites when the result is smaller, so it never grows a file and
     * is safe to re-run. Returns [oldBytes, newBytes], or null if the file is missing.
     *
     * @return array{0: int, 1: int}|null
     */
    public function recompressStored(string $path, string $type): ?array
    {
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists($path)) {
            return null;
        }

        [$width, $height, $quality] = match ($type) {
            'avatar' => [self::AVATAR_WIDTH, self::AVATAR_HEIGHT, self::AVATAR_QUALITY],
            'cover' => [self::COVER_WIDTH, self::COVER_HEIGHT, self::COVER_QUALITY],
            'portfolio' => [self::PORTFOLIO_WIDTH, self::PORTFOLIO_HEIGHT, self::PORTFOLIO_QUALITY],
            default => throw new \InvalidArgumentException("Invalid image type: {$type}"),
        };

        $original = (string) $disk->get($path);
        $oldBytes = strlen($original);

        $encoded = (string) $this->manager
            ->decode($original)
            ->orient()
            ->cover($width, $height)
            ->encodeUsingFormat(Format::WEBP, quality: $quality);

        // Only overwrite on a meaningful reduction (>= 5% smaller). This keeps re-runs a
        // true no-op and prevents generational quality loss from repeated re-encoding.
        if (strlen($encoded) <= (int) ($oldBytes * 0.95)) {
            $disk->put($path, $encoded);

            return [$oldBytes, strlen($encoded)];
        }

        return [$oldBytes, $oldBytes];
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

}
