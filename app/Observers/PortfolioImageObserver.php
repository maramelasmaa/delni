<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PortfolioImage;
use App\Services\ProfileImageService;

class PortfolioImageObserver
{
    public function __construct(private readonly ProfileImageService $imageService) {}

    public function deleted(PortfolioImage $image): void
    {
        $this->imageService->deleteImage($image->path);
    }

    public function forceDeleted(PortfolioImage $image): void
    {
        $this->imageService->deleteImage($image->path);
    }
}
