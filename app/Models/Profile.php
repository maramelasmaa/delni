<?php

namespace App\Models;

use App\Services\ProfileVisibilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'business_name', 'type', 'provider_type', 'bio', 'slug',
        'offers_remote_work', 'map_url', 'service_area_note',
        'city_id', 'category_id', 'whatsapp', 'phone',
        'experience_years', 'logo', 'cover_image', 'is_complete',
        'website', 'instagram', 'facebook', 'linkedin',
        'instagram_handle', 'facebook_slug', 'linkedin_slug', 'github_username',
        'provider_access_ends_at',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'offers_remote_work' => 'boolean',
        'provider_access_ends_at' => 'datetime',
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

    public function userFavorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
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

    public function scopeWithPublicReviewAggregates(Builder $query): Builder
    {
        return $query
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating');
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
     * - subcategories (at least one)
     */
    public function calculateCompletionPercentage(): int
    {
        $conditions = [
            'business_name' => filled($this->business_name) || filled($this->user?->name),
            'city' => $this->city_id !== null,
            'category' => $this->category_id !== null,
            'phone' => filled($this->phone),
            'whatsapp' => filled($this->whatsapp),
            'subcategory' => $this->subcategories()->exists(),
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

    protected function instagram(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes): ?string => static::canonicalInstagramUrl(
                $attributes['instagram_handle'] ?? null,
                $value,
            ),
        );
    }

    protected function facebook(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes): ?string => static::canonicalPathBasedUrl(
                'https://facebook.com/',
                $attributes['facebook_slug'] ?? null,
                $value,
            ),
        );
    }

    protected function linkedin(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes): ?string => static::canonicalPathBasedUrl(
                'https://linkedin.com/',
                $attributes['linkedin_slug'] ?? null,
                $value,
            ),
        );
    }

    protected function github(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): ?string => static::canonicalSimpleUrl(
                'https://github.com/',
                $attributes['github_username'] ?? null,
            ),
        );
    }

    protected function instagramHandle(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes): ?string => static::canonicalInstagramUrl(
                $value ?: static::normalizeInstagramHandle($attributes['instagram'] ?? null),
                $attributes['instagram'] ?? null,
            ),
            set: fn (?string $value): ?string => static::normalizeInstagramHandle($value),
        );
    }

    protected function facebookSlug(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes): ?string => static::canonicalPathBasedUrl(
                'https://facebook.com/',
                $value ?: static::normalizePathSlug($attributes['facebook'] ?? null, 'facebook.com'),
                $attributes['facebook'] ?? null,
            ),
            set: fn (?string $value): ?string => static::normalizePathSlug($value, 'facebook.com'),
        );
    }

    protected function linkedinSlug(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes): ?string => static::canonicalPathBasedUrl(
                'https://linkedin.com/',
                $value ?: static::normalizePathSlug($attributes['linkedin'] ?? null, 'linkedin.com'),
                $attributes['linkedin'] ?? null,
            ),
            set: fn (?string $value): ?string => static::normalizePathSlug($value, 'linkedin.com'),
        );
    }

    protected function githubUsername(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => static::canonicalSimpleUrl(
                'https://github.com/',
                $value,
            ),
            set: fn (?string $value): ?string => static::normalizeSimpleUsername($value, 'github.com'),
        );
    }

    public static function normalizeInstagramHandle(?string $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('#^https?://#i', $value) === 1) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));
            $path = trim((string) parse_url($value, PHP_URL_PATH), '/');

            if (! static::hostMatches($host, 'instagram.com')) {
                return null;
            }

            $value = (string) (strtok($path, '/') ?: '');
        }

        $value = ltrim($value, '@');
        $value = preg_replace('/[^A-Za-z0-9._]/', '', $value);

        return $value !== '' ? $value : null;
    }

    public static function normalizePathSlug(?string $value, string $expectedHost): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('#^https?://#i', $value) === 1) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));
            $path = trim((string) parse_url($value, PHP_URL_PATH), '/');

            if (! static::hostMatches($host, $expectedHost)) {
                return null;
            }

            $value = $path;
        }

        $value = trim($value, '/');
        $value = preg_replace('/[^A-Za-z0-9._\-\/]/', '', $value);

        return $value !== '' ? $value : null;
    }

    public static function normalizeSimpleUsername(?string $value, string $expectedHost): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('#^https?://#i', $value) === 1) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));
            $path = trim((string) parse_url($value, PHP_URL_PATH), '/');

            if (! static::hostMatches($host, $expectedHost)) {
                return null;
            }

            $value = (string) (strtok($path, '/') ?: '');
        }

        $value = ltrim($value, '@');
        $value = preg_replace('/[^A-Za-z0-9\-]/', '', $value);

        return $value !== '' ? $value : null;
    }

    private static function canonicalInstagramUrl(?string $handle, ?string $legacyUrl): ?string
    {
        $normalizedHandle = static::normalizeInstagramHandle($handle);

        if ($normalizedHandle !== null) {
            return 'https://instagram.com/'.$normalizedHandle;
        }

        return filled($legacyUrl) ? $legacyUrl : null;
    }

    private static function canonicalPathBasedUrl(string $baseUrl, ?string $path, ?string $legacyUrl): ?string
    {
        $normalizedPath = trim((string) $path, '/');

        if ($normalizedPath !== '') {
            return rtrim($baseUrl, '/').'/'.$normalizedPath;
        }

        return filled($legacyUrl) ? $legacyUrl : null;
    }

    private static function canonicalSimpleUrl(string $baseUrl, ?string $username): ?string
    {
        $normalized = trim((string) $username);

        if ($normalized === '') {
            return null;
        }

        return rtrim($baseUrl, '/').'/'.Str::of($normalized)->trim('/');
    }

    private static function hostMatches(?string $host, string $expectedHost): bool
    {
        if (! is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);
        $expectedHost = strtolower($expectedHost);

        return $host === $expectedHost || str_ends_with($host, '.'.$expectedHost);
    }
}
