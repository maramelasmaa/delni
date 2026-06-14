<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('delni:clear-public-cache')]
#[Description('Clear cached public frontend aggregates and lookup data')]
class ClearPublicFrontendCacheCommand extends Command
{
    private const KEYS = [
        'frontend.profile_counts.profiles_category_id',
        'frontend.profile_counts.profiles_city_id',
        'frontend.profile_counts.subcategory_id',
        'frontend.cta_whatsapp_url',
        'frontend.categories',
        'frontend.subcategories',
        'frontend.cities',
    ];

    public function handle(): int
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
            Cache::forget('illuminate:cache:flexible:created:'.$key);
        }

        $this->info('Public frontend cache cleared.');

        return self::SUCCESS;
    }
}
