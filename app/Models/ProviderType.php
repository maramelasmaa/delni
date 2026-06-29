<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ProviderType extends Model
{
    public const DEFAULT_OPTIONS = [
        'individual' => 'فرد',
        'company' => 'شركة',
        'agency' => 'وكالة',
        'clinic' => 'عيادة',
        'studio' => 'استوديو',
        'freelancer' => 'مستقل',
        'other' => 'أخرى',
    ];

    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getLocalizedNameAttribute(): string
    {
        if (app()->getLocale() === 'ar' && filled($this->name_ar)) {
            return $this->name_ar;
        }

        return $this->name;
    }

    protected static function booted(): void
    {
        // Provider types change rarely (admin only) but options() is called on every
        // list endpoint. Bust the cache whenever a row is written or removed.
        static::saved(fn () => self::flushOptionsCache());
        static::deleted(fn () => self::flushOptionsCache());
    }

    public static function flushOptionsCache(): void
    {
        foreach (['ar', 'en'] as $locale) {
            Cache::forget("provider_types.options.1.{$locale}");
            Cache::forget("provider_types.options.0.{$locale}");
        }
    }

    /** @return array<string, string> */
    public static function options(bool $activeOnly = true): array
    {
        // Cached (stale-while-revalidate) — this is called on home/search/category/
        // subcategory/top-rated. Without caching it ran a Schema::hasTable() metadata
        // query + a select on every list request. Keyed by locale because
        // localized_name depends on app()->getLocale().
        $key = sprintf('provider_types.options.%d.%s', (int) $activeOnly, app()->getLocale());

        return Cache::flexible($key, [300, 900], fn (): array => self::resolveOptions($activeOnly));
    }

    /** @return array<string, string> */
    private static function resolveOptions(bool $activeOnly): array
    {
        try {
            if (! Schema::hasTable('provider_types')) {
                return self::DEFAULT_OPTIONS;
            }

            $query = self::query()->orderBy('sort_order')->orderBy('name');

            if ($activeOnly) {
                $query->where('is_active', true);
            }

            $options = $query
                ->get()
                ->mapWithKeys(fn (self $type): array => [$type->code => $type->localized_name])
                ->all();

            return $options !== [] ? $options : self::DEFAULT_OPTIONS;
        } catch (Throwable) {
            return self::DEFAULT_OPTIONS;
        }
    }

    public static function labelFor(?string $code): string
    {
        if (! $code) {
            return '-';
        }

        return self::options(activeOnly: false)[$code] ?? $code;
    }
}
