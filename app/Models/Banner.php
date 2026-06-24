<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BannerLinkType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'link_type',
        'link_value',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    /** @return array<string, mixed> */
    protected function casts(): array
    {
        return [
            'link_type' => BannerLinkType::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'http://')) {
            return null;
        }

        return asset('storage/'.$this->image);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
