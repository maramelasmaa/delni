<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'title',
        'short_description',
        'description',
        'main_url',
        'link',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('sort_order');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if ($model->profile) {
                // Use exists() check instead of count() for better performance
                $existingCount = $model->profile->portfolioItems()
                    ->limit(2)
                    ->count();

                if ($existingCount >= 2) {
                    throw new \Exception('Portfolio limit reached. Maximum 2 projects allowed per profile.');
                }
            }
        });
    }
}
