<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for all marketplace ranking logic.
 *
 * Ranking hierarchy:
 * 1. Homepage Featured on the homepage only (is_homepage_featured=1 AND homepage_featured_until >= today)
 * 2. Top Rated Provider (profile stats meet review count and rating rules)
 * 3. Normal Provider
 *
 * Within each tier, sorted by: stored rating average DESC, stored review count DESC, created_at DESC
 */
class MarketplaceRankingService
{
    public const MIN_TOP_RATED_REVIEWS = 5;

    public const MIN_TOP_RATED_RATING = 4.5;

    /**
     * Apply ranking to a profile query for homepage display.
     */
    public function applyHomepageRanking(Builder $query): Builder
    {
        return $query
            ->addSelect(DB::raw($this->homepageBucketExpression()))
            ->orderBy('bucket', 'desc')
            ->orderByDesc('profile_stats.rating_avg')
            ->orderByDesc('profile_stats.reviews_count')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Apply ranking to a profile query for search results.
     */
    public function applySearchRanking(Builder $query): Builder
    {
        return $query
            ->addSelect(DB::raw($this->standardBucketExpression()))
            ->orderBy('bucket', 'desc')
            ->orderByDesc('profile_stats.rating_avg')
            ->orderByDesc('profile_stats.reviews_count')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Apply ranking to a profile query for category listing.
     */
    public function applyCategoryRanking(Builder $query): Builder
    {
        return $query
            ->addSelect(DB::raw($this->standardBucketExpression()))
            ->orderBy('bucket', 'desc')
            ->orderByDesc('profile_stats.rating_avg')
            ->orderByDesc('profile_stats.reviews_count')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Apply ranking to a profile query for subcategory listing.
     */
    public function applySubcategoryRanking(Builder $query): Builder
    {
        return $query
            ->addSelect(DB::raw($this->standardBucketExpression()))
            ->orderBy('bucket', 'desc')
            ->orderByDesc('profile_stats.rating_avg')
            ->orderByDesc('profile_stats.reviews_count')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Filter to providers with an active, non-expired homepage placement (paid visibility only).
     */
    public function applyHomepageFeaturedOnly(Builder $query): Builder
    {
        $today = $this->todaySql();

        return $query
            ->where('profile_stats.is_homepage_featured', 1)
            ->whereRaw("profile_stats.homepage_featured_until >= {$today}")
            ->orderByDesc('profile_stats.rating_avg')
            ->orderByDesc('profile_stats.reviews_count')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Apply live top-rated eligibility for public top-rated sections.
     */
    public function applyTopRatedEligibility(Builder $query): Builder
    {
        return $query
            ->whereRaw($this->topRatedPredicate())
            ->orderByDesc('profile_stats.rating_avg')
            ->orderByDesc('profile_stats.reviews_count')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Bucket expression for homepage ranking.
     *
     * Bucket 3: Homepage Featured (expirable)
     * Bucket 2: Top Rated Provider (stored profile stats eligibility)
     * Bucket 1: Normal Provider
     */
    private function homepageBucketExpression(): string
    {
        $today = $this->todaySql();

        return "
            CASE
                WHEN profile_stats.is_homepage_featured = 1
                     AND profile_stats.homepage_featured_until >= {$today}
                THEN 3
                WHEN {$this->topRatedPredicate()}
                THEN 2
                ELSE 1
            END AS bucket
        ";
    }

    /**
     * Bucket expression for non-homepage public listings.
     */
    private function standardBucketExpression(): string
    {
        return "
            CASE
                WHEN {$this->topRatedPredicate()}
                THEN 2
                ELSE 1
            END AS bucket
        ";
    }

    private function todaySql(): string
    {
        return DB::getPdo()->quote(now()->toDateString());
    }

    private function topRatedPredicate(): string
    {
        return sprintf(
            '(profile_stats.reviews_count >= %d AND profile_stats.rating_avg >= %.1F)',
            self::MIN_TOP_RATED_REVIEWS,
            self::MIN_TOP_RATED_RATING,
        );
    }
}
