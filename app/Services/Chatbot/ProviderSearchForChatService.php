<?php

namespace App\Services\Chatbot;

use App\Data\ProviderChatResultDTO;
use App\Models\Profile;
use App\Services\MarketplaceRankingService;
use App\Services\ProfileVisibilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Safe provider search service for chatbot.
 *
 * This service ensures that:
 * 1. Only visible profiles are returned
 * 2. Ranking respects marketplace rules
 * 3. No hidden/private data leaks
 * 4. All visibility rules are enforced
 *
 * This is the SINGLE SOURCE OF TRUTH for chatbot provider search.
 */
class ProviderSearchForChatService
{
    public function __construct(
        private ProfileVisibilityService $visibilityService,
        private MarketplaceRankingService $rankingService,
    ) {}

    /**
     * Search for providers matching optional criteria.
     *
     * @return Collection<int, ProviderChatResultDTO>
     */
    public function search(
        ?int $cityId = null,
        ?int $categoryId = null,
        ?int $subcategoryId = null,
        ?string $searchQuery = null,
        ?bool $remoteAllowed = null,
    ): Collection {
        $query = $this->buildBaseQuery();

        if ($cityId !== null) {
            $query->where('profiles.city_id', $cityId);
        }

        if ($categoryId !== null) {
            $query->where('profiles.category_id', $categoryId);
        }

        if ($subcategoryId !== null) {
            $query->whereHas('subcategories', function (Builder $q) use ($subcategoryId): void {
                $q->where('subcategories.id', $subcategoryId);
            });
        }

        if (filled($searchQuery)) {
            $searchTerm = "%{$searchQuery}%";
            $query->where(function (Builder $q) use ($searchTerm): void {
                $q->where('profiles.business_name', 'like', $searchTerm)
                    ->orWhere('profiles.bio', 'like', $searchTerm)
                    ->orWhere('categories.name', 'like', $searchTerm)
                    ->orWhere('categories.name_ar', 'like', $searchTerm);
            });
        }

        if ($remoteAllowed === true) {
            $query->where('profiles.offers_remote_work', true);
        }

        // Apply marketplace ranking
        $this->rankingService->applySearchRanking($query);

        return $query
            ->with(['user', 'category', 'city', 'subcategories', 'stats', 'approvedReviews'])
            ->get()
            ->map(fn (Profile $profile) => ProviderChatResultDTO::from($profile))
            ->values();
    }

    /**
     * Get featured providers (homepage featured or top search) for chatbot home.
     *
     * @return Collection<int, ProviderChatResultDTO>
     */
    public function getFeaturedProviders(int $limit = 6): Collection
    {
        $query = $this->buildBaseQuery();

        $this->rankingService->applyHomepageRanking($query);

        return $query
            ->with(['user', 'category', 'city', 'subcategories', 'stats', 'approvedReviews'])
            ->limit($limit)
            ->get()
            ->map(fn (Profile $profile) => ProviderChatResultDTO::from($profile))
            ->values();
    }

    /**
     * Build base query with all visibility conditions pre-applied.
     *
     * This is the foundation query that ALL search operations build on.
     * Ensures visibility rules are NEVER bypassed.
     */
    private function buildBaseQuery(): Builder
    {
        return Profile::query()
            ->join('users', 'profiles.user_id', '=', 'users.id')
            ->join('profile_stats', 'profiles.id', '=', 'profile_stats.profile_id')
            ->leftJoin('categories', 'profiles.category_id', '=', 'categories.id')
            ->leftJoin('cities', 'profiles.city_id', '=', 'cities.id')
            ->select('profiles.*')
            ->visible();
    }
}
