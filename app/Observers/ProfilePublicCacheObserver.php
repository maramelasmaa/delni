<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Profile;
use Illuminate\Support\Facades\Cache;

class ProfilePublicCacheObserver
{
    public function created(Profile $profile): void
    {
        $this->clearPublicCache();
    }

    public function updated(Profile $profile): void
    {
        if ($profile->isDirty(['is_complete', 'category_id', 'city_id'])) {
            $this->clearPublicCache();
        }
    }

    public function deleted(Profile $profile): void
    {
        $this->clearPublicCache();
    }

    private function clearPublicCache(): void
    {
        Cache::forget('frontend.profile_counts.profiles_category_id');
        Cache::forget('frontend.profile_counts.profiles_city_id');
        Cache::forget('frontend.profile_counts.subcategory_id');
    }
}
