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
            ->with('user')
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
            $normalizedKeyword = $this->normalization->normalize($filters->keyword);
            $rawKeyword = '%'.addcslashes($filters->keyword, '%_\\').'%';

            $query->where(function (Builder $q) use ($normalizedKeyword, $rawKeyword): void {
                // For 3+ character queries on MySQL, use FULLTEXT MATCH AGAINST on the profile
                // columns — MySQL's inverted index avoids the full-table scan caused by LIKE %keyword%.
                // SQLite (used in tests) does not support FULLTEXT, so fall back to prefix LIKE.
                // See: https://laravel.com/docs/13.x/queries#full-text-where-clauses
                // On MySQL with 3+ chars, FULLTEXT MATCH AGAINST uses an inverted index and avoids
                // the full-table scan that LIKE %keyword% (leading wildcard) would cause.
                // On MySQL with < 3 chars, prefix LIKE (keyword%) still uses the B-tree index.
                // On SQLite (tests), FULLTEXT is unsupported so fall back to both-side LIKE.
                // See: https://laravel.com/docs/13.x/queries#full-text-where-clauses
                $isMysql = DB::connection()->getDriverName() === 'mysql';
                if ($isMysql && mb_strlen($normalizedKeyword) >= 2) {
                    // Boolean mode with per-word prefix wildcard (*) — enables partial/prefix
                    // matching so "مصم" finds "مصمم", "graph" finds "graphic", etc.
                    // Natural language mode only matches whole indexed words; boolean + * fixes that.
                    $booleanTerm = collect(preg_split('/\s+/', trim($normalizedKeyword)) ?: [])
                        ->filter(fn (string $w) => mb_strlen($w) >= 2)
                        ->map(fn (string $w) => addcslashes($w, '"\\').'*')
                        ->join(' ');

                    if ($booleanTerm !== '') {
                        $q->whereFullText(['search_business_name', 'search_bio'], $booleanTerm, ['mode' => 'boolean']);
                    }
                } else {
                    $wildcardKeyword = '%'.addcslashes($normalizedKeyword, '%_\\').'%';
                    $q->where('profiles.search_business_name', 'like', $wildcardKeyword)
                        ->orWhere('profiles.search_bio', 'like', $wildcardKeyword);
                }

                // Subcategory/category/city tables are small — LIKE is fine here
                $q->orWhereExists(function ($sub) use ($normalizedKeyword, $rawKeyword): void {
                    $keyword = '%'.addcslashes($normalizedKeyword, '%_\\').'%';
                    $sub->select(DB::raw(1))
                        ->from('profile_subcategory')
                        ->join('subcategories', 'subcategories.id', '=', 'profile_subcategory.subcategory_id')
                        ->whereColumn('profile_subcategory.profile_id', 'profiles.id')
                        ->where('subcategories.is_active', true)
                        ->where(function ($q2) use ($keyword, $rawKeyword): void {
                            $q2->where('subcategories.search_name', 'like', $keyword)
                                ->orWhere('subcategories.name', 'like', $rawKeyword);
                        });
                })
                    ->orWhereExists(function ($sub) use ($normalizedKeyword, $rawKeyword): void {
                        $keyword = '%'.addcslashes($normalizedKeyword, '%_\\').'%';
                        $sub->select(DB::raw(1))
                            ->from('categories')
                            ->whereColumn('categories.id', 'profiles.category_id')
                            ->where('categories.is_active', true)
                            ->where(function ($q2) use ($keyword, $rawKeyword): void {
                                $q2->where('categories.name_ar', 'like', $keyword)
                                    ->orWhere('categories.name_ar', 'like', $rawKeyword)
                                    ->orWhere('categories.name', 'like', $rawKeyword);
                            });
                    })
                    ->orWhereExists(function ($sub) use ($normalizedKeyword, $rawKeyword): void {
                        $keyword = '%'.addcslashes($normalizedKeyword, '%_\\').'%';
                        $sub->select(DB::raw(1))
                            ->from('cities')
                            ->whereColumn('cities.id', 'profiles.city_id')
                            ->where('cities.is_active', true)
                            ->where(function ($q2) use ($keyword, $rawKeyword): void {
                                $q2->where('cities.name_ar', 'like', $keyword)
                                    ->orWhere('cities.name', 'like', $rawKeyword);
                            });
                    });
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
