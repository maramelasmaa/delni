<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Models\Profile;
use App\Services\ArabicNormalizationService;
use App\Services\MarketplaceRankingService;
use App\Services\ProfileVisibilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProviderSearchForChatService
{
    public function __construct(
        private readonly MarketplaceRankingService $ranking,
        private readonly ProfileVisibilityService $visibility,
        private readonly ArabicNormalizationService $normalization,
    ) {}

    /**
     * @param  array<string, mixed>  $intent
     * @return Collection<int, Profile>
     */
    public function search(array $intent): Collection
    {
        $queryData = $intent['query'];

        $query = Profile::query()
            ->select('profiles.*')
            ->join('users', 'users.id', '=', 'profiles.user_id')
            ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id')
            ->leftJoin('categories', 'categories.id', '=', 'profiles.category_id')
            ->leftJoin('cities', 'cities.id', '=', 'profiles.city_id');

        $this->visibility->applyVisibleQuery($query);
        $this->applySearchTerms($query, $queryData);
        $this->applyRelevance($query, $queryData);
        $this->ranking->applySearchRanking($query);

        return $query->limit(5)->get()->load(['user', 'city', 'category', 'stats']);
    }

    /**
     * @param  Builder<Profile>  $query
     * @param  array<string, mixed>  $queryData
     */
    private function applySearchTerms(Builder $query, array $queryData): void
    {
        if ($queryData['city_id'] !== null) {
            $query->where('profiles.city_id', $queryData['city_id']);
        }

        if ($queryData['category_id'] !== null) {
            $query->where('profiles.category_id', $queryData['category_id']);
        }

        if ($queryData['subcategory_id'] !== null) {
            $query->whereExists(function ($sub) use ($queryData): void {
                $sub->select(DB::raw(1))
                    ->from('profile_subcategory')
                    ->whereColumn('profile_subcategory.profile_id', 'profiles.id')
                    ->where('profile_subcategory.subcategory_id', $queryData['subcategory_id']);
            });
        }

        if ($queryData['min_experience_years'] !== null) {
            $query->where('profiles.experience_years', '>=', $queryData['min_experience_years']);
        }

        $term = $queryData['provider_name'] ?: $queryData['service'];
        if (filled($term)) {
            $like = '%'.addcslashes($this->normalization->normalize((string) $term), '%_\\').'%';
            $rawLike = '%'.addcslashes((string) $term, '%_\\').'%';

            $query->where(function (Builder $q) use ($like, $rawLike): void {
                $q->where('profiles.search_business_name', 'like', $like)
                    ->orWhere('profiles.search_bio', 'like', $like)
                    ->orWhere('profiles.business_name', 'like', $rawLike)
                    ->orWhere('profiles.slug', 'like', $rawLike)
                    ->orWhere('profiles.provider_type', 'like', $rawLike)
                    ->orWhere('users.name', 'like', $rawLike)
                    ->orWhere('categories.name', 'like', $rawLike)
                    ->orWhere('categories.name_ar', 'like', $rawLike)
                    ->orWhereExists(function ($sub) use ($rawLike): void {
                        $sub->select(DB::raw(1))
                            ->from('profile_subcategory')
                            ->join('subcategories', 'subcategories.id', '=', 'profile_subcategory.subcategory_id')
                            ->whereColumn('profile_subcategory.profile_id', 'profiles.id')
                            ->where(function ($nested) use ($rawLike): void {
                                $nested->where('subcategories.name', 'like', $rawLike)
                                    ->orWhere('subcategories.name_ar', 'like', $rawLike);
                            });
                    });
            });
        }
    }

    /**
     * @param  Builder<Profile>  $query
     * @param  array<string, mixed>  $queryData
     */
    private function applyRelevance(Builder $query, array $queryData): void
    {
        $name = $queryData['provider_name'];
        if (! filled($name)) {
            return;
        }

        $like = '%'.addcslashes((string) $name, '%_\\').'%';

        $query->orderByRaw(
            "CASE
                WHEN profiles.business_name = ? THEN 100
                WHEN users.name = ? THEN 95
                WHEN profiles.business_name LIKE ? THEN 80
                WHEN users.name LIKE ? THEN 75
                ELSE 0
            END DESC",
            [$name, $name, $like, $like],
        );
    }
}
