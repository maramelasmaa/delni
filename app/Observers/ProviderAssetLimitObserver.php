<?php

namespace App\Observers;

use App\Models\PortfolioImage;
use App\Models\PortfolioItem;
use App\Models\ProviderLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProviderAssetLimitObserver
{
    public function saving(ProviderLink|PortfolioImage|PortfolioItem $model): void
    {
        if ($model instanceof ProviderLink) {
            $this->enforceProviderLinks($model);
        }

        if ($model instanceof PortfolioImage) {
            $this->enforcePortfolioImages($model);
        }

        if ($model instanceof PortfolioItem) {
            $this->enforcePortfolioItems($model);
        }
    }

    private function enforceProviderLinks(ProviderLink $link): void
    {
        if ($link->is_active === false) {
            return;
        }

        DB::transaction(function () use ($link): void {
            $count = ProviderLink::query()
                ->where('profile_id', $link->profile_id)
                ->where('is_active', true)
                ->when($link->exists, fn ($query) => $query->whereKeyNot($link->getKey()))
                ->lockForUpdate()
                ->count();

            if ($count >= 10) {
                throw ValidationException::withMessages(['links' => 'لا يمكن إضافة أكثر من 10 روابط نشطة.']);
            }
        });
    }

    private function enforcePortfolioImages(PortfolioImage $image): void
    {
        DB::transaction(function () use ($image): void {
            $count = PortfolioImage::query()
                ->where('portfolio_item_id', $image->portfolio_item_id)
                ->when($image->exists, fn ($query) => $query->whereKeyNot($image->getKey()))
                ->lockForUpdate()
                ->count();

            if ($count >= 4) {
                throw ValidationException::withMessages(['images' => 'لا يمكن إضافة أكثر من 4 صور لكل خدمة أو عمل.']);
            }
        });
    }

    private function enforcePortfolioItems(PortfolioItem $item): void
    {
        DB::transaction(function () use ($item): void {
            $count = PortfolioItem::query()
                ->where('profile_id', $item->profile_id)
                ->when($item->exists, fn ($query) => $query->whereKeyNot($item->getKey()))
                ->lockForUpdate()
                ->count();

            if ($count >= 2) {
                throw ValidationException::withMessages(['portfolio' => 'يمكنك إضافة خدمتين أو عملين فقط في هذه المرحلة.']);
            }
        });
    }
}
