<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    /** @return array<string, string> */
    public static function options(bool $activeOnly = true): array
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
