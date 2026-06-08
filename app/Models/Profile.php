<?php

namespace App\Models;

use App\Services\ProfileVisibilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'business_name', 'type', 'provider_type', 'bio', 'slug',
        'offers_remote_work', 'map_url', 'service_area_note',
        'city_id', 'category_id', 'whatsapp', 'phone',
        'experience_years', 'logo', 'cover_image', 'is_complete',
        'website', 'instagram', 'facebook', 'linkedin',
    ];

    protected $with = ['user'];

    protected $casts = [
        'is_complete' => 'boolean',
        'offers_remote_work' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategories(): BelongsToMany
    {
        return $this->belongsToMany(Subcategory::class, 'profile_subcategory', 'profile_id', 'subcategory_id')->withTimestamps();
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ProfileStats::class, 'profile_id', 'id');
    }

    public function portfolioItems(): HasMany
    {
        return $this->hasMany(PortfolioItem::class)->orderBy('sort_order');
    }

    public function links(): HasMany
    {
        return $this->hasMany(ProviderLink::class)->orderBy('sort_order');
    }

    public function activeLinks(): HasMany
    {
        return $this->hasMany(ProviderLink::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(ProviderCredential::class)->orderByDesc('issue_date')->orderBy('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    // Scopes

    /**
     * Scope: Filter to only discoverable (visible) profiles.
     *
     * Applies the visibility rules from ProfileVisibilityService.
     * Requires 'users' and 'profile_stats' joins to be present on the query.
     *
     * Example:
     *   Profile::join('users', ...)->join('profile_stats', ...)->visible()
     */
    public function scopeVisible(Builder $query): Builder
    {
        return app(ProfileVisibilityService::class)->applyVisibleQuery($query);
    }

    // Helpers

    public function isDiscoverable(): bool
    {
        return app(ProfileVisibilityService::class)->isDiscoverable($this);
    }

    /**
     * Calculate profile completion percentage based on required fields.
     * Returns value between 0-100.
     *
     * Required fields:
     * - business_name OR user->name
     * - city_id
     * - category_id
     * - phone
     * - whatsapp
     */
    public function calculateCompletionPercentage(): int
    {
        $conditions = [
            'business_name' => filled($this->business_name) || filled($this->user?->name),
            'city' => $this->city_id !== null,
            'category' => $this->category_id !== null,
            'phone' => filled($this->phone),
            'whatsapp' => filled($this->whatsapp),
        ];

        return (int) (count(array_filter($conditions)) / count($conditions) * 100);
    }

    /**
     * Update is_complete flag based on completion percentage.
     * Called automatically when profile is 100% complete.
     */
    public function updateCompletionFlag(): void
    {
        $percentage = $this->calculateCompletionPercentage();
        $this->update(['is_complete' => $percentage === 100]);
    }
}
