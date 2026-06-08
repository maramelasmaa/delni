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
 * 1. Homepage Featured (is_homepage_featured=1 AND homepage_featured_until >= today)
 * 2. Top Search (is_top_search=1 AND top_search_until >= today)
 * 3. Top Category (is_top_category=1 AND top_category_until >= today)
 * 4. Top Subcategory (is_top_subcategory=1 AND top_subcategory_until >= today)
 * 5. Featured Provider (is_featured=1 AND featured_until >= today)
 * 6. Top Rated Provider (approved reviews meet live count and rating rules)
 * 7. Normal Provider
 *
 * Within each tier, sorted by: approved review average DESC, approved review count DESC, created_at DESC
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
            ->addSelect(DB::raw($this->bucketExpression()))
            ->orderBy('bucket', 'desc')
            ->orderByRaw($this->approvedRatingAverageExpression().' DESC')
            ->orderByRaw($this->approvedReviewCountExpression().' DESC')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Apply ranking to a profile query for search results.
     */
    public function applySearchRanking(Builder $query): Builder
    {
        return $query
            ->addSelect(DB::raw($this->bucketExpression()))
            ->orderBy('bucket', 'desc')
            ->orderByRaw("
                CASE
                    WHEN profile_stats.is_featured = 1
                         AND profile_stats.featured_until >= {$this->todaySql()}
                    THEN profile_stats.featured_until
                    ELSE NULL
                END DESC
            ")
            ->orderByRaw($this->approvedRatingAverageExpression().' DESC')
            ->orderByRaw($this->approvedReviewCountExpression().' DESC')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Apply ranking to a profile query for category listing.
     */
    public function applyCategoryRanking(Builder $query): Builder
    {
        return $query
            ->addSelect(DB::raw($this->categoryBucketExpression()))
            ->orderBy('bucket', 'desc')
            ->orderByRaw($this->approvedRatingAverageExpression().' DESC')
            ->orderByRaw($this->approvedReviewCountExpression().' DESC')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Apply ranking to a profile query for subcategory listing.
     */
    public function applySubcategoryRanking(Builder $query): Builder
    {
        return $query
            ->addSelect(DB::raw($this->subcategoryBucketExpression()))
            ->orderBy('bucket', 'desc')
            ->orderByRaw($this->approvedRatingAverageExpression().' DESC')
            ->orderByRaw($this->approvedReviewCountExpression().' DESC')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Apply live top-rated eligibility for public top-rated sections.
     */
    public function applyTopRatedEligibility(Builder $query): Builder
    {
        return $query
            ->whereRaw($this->topRatedPredicate())
            ->orderByRaw($this->approvedRatingAverageExpression().' DESC')
            ->orderByRaw($this->approvedReviewCountExpression().' DESC')
            ->orderBy('profiles.created_at', 'desc');
    }

    /**
     * Main bucket expression for global ranking (homepage, search).
     *
     * Bucket 7: Homepage Featured (expirable)
     * Bucket 6: Top Search (expirable)
     * Bucket 5: Top Category (expirable)
     * Bucket 4: Top Subcategory (expirable)
     * Bucket 3: Featured Provider (expirable)
     * Bucket 2: Top Rated Provider (live approved-review eligibility)
     * Bucket 1: Normal Provider
     */
    private function bucketExpression(): string
    {
        $today = $this->todaySql();

        return "
            CASE
                WHEN profile_stats.is_homepage_featured = 1
                     AND profile_stats.homepage_featured_until >= {$today}
                THEN 7
                WHEN profile_stats.is_top_search = 1
                     AND profile_stats.top_search_until >= {$today}
                THEN 6
                WHEN profile_stats.is_top_category = 1
                     AND profile_stats.top_category_until >= {$today}
                THEN 5
                WHEN profile_stats.is_top_subcategory = 1
                     AND profile_stats.top_subcategory_until >= {$today}
                THEN 4
                WHEN profile_stats.is_featured = 1
                     AND profile_stats.featured_until >= {$today}
                THEN 3
                WHEN {$this->topRatedPredicate()}
                THEN 2
                ELSE 1
            END AS bucket
        ";
    }

    /**
     * Bucket expression for category-specific ranking.
     *
     * Bucket 5: Top Category (same category)
     * Bucket 4: Top Subcategory (same subcategory)
     * Bucket 3: Featured Provider
     * Bucket 2: Top Rated
     * Bucket 1: Normal
     */
    private function categoryBucketExpression(): string
    {
        $today = $this->todaySql();

        return "
            CASE
                WHEN profile_stats.is_top_category = 1
                     AND profile_stats.top_category_until >= {$today}
                THEN 5
                WHEN profile_stats.is_featured = 1
                     AND profile_stats.featured_until >= {$today}
                THEN 3
                WHEN {$this->topRatedPredicate()}
                THEN 2
                ELSE 1
            END AS bucket
        ";
    }

    /**
     * Bucket expression for subcategory-specific ranking.
     *
     * Bucket 4: Top Subcategory (same subcategory)
     * Bucket 3: Featured Provider
     * Bucket 2: Top Rated
     * Bucket 1: Normal
     */
    private function subcategoryBucketExpression(): string
    {
        $today = $this->todaySql();

        return "
            CASE
                WHEN profile_stats.is_top_subcategory = 1
                     AND profile_stats.top_subcategory_until >= {$today}
                THEN 4
                WHEN profile_stats.is_featured = 1
                     AND profile_stats.featured_until >= {$today}
                THEN 3
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
            '(%s >= %d AND %s >= %.1F)',
            $this->approvedReviewCountExpression(),
            self::MIN_TOP_RATED_REVIEWS,
            $this->approvedRatingAverageExpression(),
            self::MIN_TOP_RATED_RATING,
        );
    }

    private function approvedReviewCountExpression(): string
    {
        return "(
            SELECT COUNT(*)
            FROM reviews
            WHERE reviews.profile_id = profiles.id
              AND reviews.status = 'approved'
              AND reviews.deleted_at IS NULL
        )";
    }

    private function approvedRatingAverageExpression(): string
    {
        return "COALESCE((
            SELECT AVG(reviews.rating)
            FROM reviews
            WHERE reviews.profile_id = profiles.id
              AND reviews.status = 'approved'
              AND reviews.deleted_at IS NULL
        ), 0)";
    }
}
