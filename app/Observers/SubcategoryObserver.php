<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Subcategory;
use App\Services\ArabicNormalizationService;

class SubcategoryObserver
{
    public function __construct(
        private readonly ArabicNormalizationService $normalization,
    ) {}

    public function created(Subcategory $subcategory): void
    {
        $this->updateSearchName($subcategory);
    }

    public function updated(Subcategory $subcategory): void
    {
        if ($subcategory->wasChanged('name_ar')) {
            $this->updateSearchName($subcategory);
        }
    }

    private function updateSearchName(Subcategory $subcategory): void
    {
        Subcategory::where('id', $subcategory->id)->update([
            'search_name' => $this->normalization->normalize($subcategory->name_ar),
        ]);
    }
}
