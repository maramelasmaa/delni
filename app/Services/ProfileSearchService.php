<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ProfileSearchFilters;
use App\Models\Profile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProfileSearchService
{
    public function __construct(
        private MarketplaceRankingService $rankingService,
        private ProfileVisibilityService $visibilityService,
        private ArabicNormalizationService $normalization,
    ) {}

    public function search(ProfileSearchFilters $filters): LengthAwarePaginator
    {
        $query = Profile::query()
            ->select('profiles.*')
            ->withPublicReviewAggregates()
            ->join('users', 'users.id', '=', 'profiles.user_id')
            ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id');

        // Apply visibility conditions from ProfileVisibilityService (single source of truth)
        $query = $this->visibilityService->applyVisibleQuery($query);

        $this->applyFilters($query, $filters);

        if ($filters->sort !== null) {
            $this->applySort($query, $filters->sort);
        } else {
            $query = $this->rankingService->applySearchRanking($query);
        }

        $paginator = $query->paginate(
            perPage: $filters->perPage,
            page: $filters->page,
        );

        // Load relationships on already-filtered, paginated result set — not on base query.
        $paginator->getCollection()->load(['stats', 'city', 'category']);

        return $paginator;
    }

    private function applyFilters(Builder $query, ProfileSearchFilters $filters): void
    {
        if ($filters->cityId !== null) {
            $query->where('profiles.city_id', $filters->cityId);
        }

        if ($filters->categoryId !== null) {
            $query->where('profiles.category_id', $filters->categoryId);
        }

        if ($filters->subcategoryId !== null) {
            $query->whereExists(function ($sub) use ($filters): void {
                $sub->select(DB::raw(1))
                    ->from('profile_subcategory')
                    ->whereColumn('profile_subcategory.profile_id', 'profiles.id')
                    ->where('profile_subcategory.subcategory_id', $filters->subcategoryId);
            });
        }

        if ($filters->providerType !== null) {
            $query->where('profiles.provider_type', $filters->providerType);
        }

        if ($filters->remote) {
            $query->where('profiles.offers_remote_work', true);
        }

        if ($filters->keyword !== null) {
            // Normalize search keyword for Arabic matching
            // This allows users to find providers regardless of:
            // - Hamza placement (أحمد, احمد, أحمد all match)
            // - Diacritics (تقنيّة, تقنية match)
            // - Final ta variants (تقنيه, تقنية match)
            // - Other Arabic variants
            $normalizedKeyword = $this->normalization->normalize($filters->keyword);
            $keyword = '%'.addcslashes($normalizedKeyword, '%_\\').'%';

            $query->where(function (Builder $q) use ($keyword): void {
                $q->where('profiles.search_business_name', 'like', $keyword)
                    ->orWhere('profiles.search_bio', 'like', $keyword);
            });
        }
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'rating' => $query
                ->orderByDesc('profile_stats.rating_avg')
                ->orderByDesc('profile_stats.reviews_count')
                ->orderByDesc('profiles.id'),
            'reviews' => $query
                ->orderByDesc('profile_stats.reviews_count')
                ->orderByDesc('profile_stats.rating_avg')
                ->orderByDesc('profiles.id'),
            'newest' => $query
                ->orderByDesc('profiles.created_at')
                ->orderByDesc('profiles.id'),
            default => null,
        };
    }
}
