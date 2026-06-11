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
     * Multi-layer semantic search for provider entity names and services.
     *
     * Search priority:
     * 1. Exact provider/business name match
     * 2. Partial provider/business name match
     * 3. Semantic provider search (bio, descriptions)
     * 4. Category/subcategory search
     * 5. Fallback matches
     *
     * @return Collection<int, ProviderChatResultDTO>
     */
    public function searchSemantic(
        ?string $providerNameQuery = null,
        ?string $businessNameQuery = null,
        ?string $serviceQuery = null,
        ?int $cityId = null,
        ?int $categoryHint = null,
    ): Collection {
        // Try exact provider name first
        if (filled($providerNameQuery)) {
            $exact = $this->searchByProviderName($providerNameQuery, $cityId);
            if ($exact->isNotEmpty()) {
                return $exact;
            }
        }

        // Try exact business name
        if (filled($businessNameQuery)) {
            $exact = $this->searchByBusinessName($businessNameQuery, $cityId);
            if ($exact->isNotEmpty()) {
                return $exact;
            }
        }

        // Try mixed entity + service search
        if (filled($providerNameQuery) || filled($businessNameQuery)) {
            $mixed = $this->searchProviderEntity(
                $providerNameQuery ?? $businessNameQuery,
                $serviceQuery,
                $cityId,
                $categoryHint,
            );
            if ($mixed->isNotEmpty()) {
                return $mixed;
            }
        }

        // Try service-only search with hints
        if (filled($serviceQuery)) {
            return $this->searchByService($serviceQuery, $cityId, $categoryHint);
        }

        return collect();
    }

    /**
     * Search by provider name (user.name).
     *
     * @return Collection<int, ProviderChatResultDTO>
     */
    public function searchByProviderName(string $name, ?int $cityId = null): Collection
    {
        $query = $this->buildBaseQuery();

        // Exact match first
        $searchTerm = "%{$name}%";
        $query->where('users.name', 'like', $searchTerm);

        if ($cityId !== null) {
            $query->where('profiles.city_id', $cityId);
        }

        $this->rankingService->applySearchRanking($query);

        return $query
            ->with(['user', 'category', 'city', 'subcategories', 'stats', 'approvedReviews'])
            ->get()
            ->map(fn (Profile $profile) => ProviderChatResultDTO::from($profile))
            ->values();
    }

    /**
     * Search by business name.
     *
     * @return Collection<int, ProviderChatResultDTO>
     */
    public function searchByBusinessName(string $businessName, ?int $cityId = null): Collection
    {
        $query = $this->buildBaseQuery();

        $searchTerm = "%{$businessName}%";
        $query->where('profiles.business_name', 'like', $searchTerm);

        if ($cityId !== null) {
            $query->where('profiles.city_id', $cityId);
        }

        $this->rankingService->applySearchRanking($query);

        return $query
            ->with(['user', 'category', 'city', 'subcategories', 'stats', 'approvedReviews'])
            ->get()
            ->map(fn (Profile $profile) => ProviderChatResultDTO::from($profile))
            ->values();
    }

    /**
     * Search provider entity (name + possible service hint).
     *
     * Searches across:
     * - provider name
     * - business name
     * - profile slug
     * - bio
     * - services
     *
     * @return Collection<int, ProviderChatResultDTO>
     */
    public function searchProviderEntity(
        string $entity,
        ?string $serviceHint = null,
        ?int $cityId = null,
        ?int $categoryHint = null,
    ): Collection {
        $query = $this->buildBaseQuery();

        $searchTerm = "%{$entity}%";

        // Search entity across multiple provider fields
        $query->where(function (Builder $q) use ($searchTerm) {
            $q->where('users.name', 'like', $searchTerm)
                ->orWhere('profiles.business_name', 'like', $searchTerm)
                ->orWhere('profiles.slug', 'like', $searchTerm)
                ->orWhere('profiles.bio', 'like', $searchTerm);
        });

        // If service hint provided, boost category match
        if (filled($serviceHint) || $categoryHint !== null) {
            $query->where(function (Builder $q) use ($serviceHint, $categoryHint) {
                if ($categoryHint !== null) {
                    $q->where('profiles.category_id', $categoryHint);
                }
                if (filled($serviceHint)) {
                    $q->orWhere('profiles.bio', 'like', "%{$serviceHint}%");
                }
            });
        }

        if ($cityId !== null) {
            $query->where('profiles.city_id', $cityId);
        }

        $this->rankingService->applySearchRanking($query);

        return $query
            ->with(['user', 'category', 'city', 'subcategories', 'stats', 'approvedReviews'])
            ->get()
            ->map(fn (Profile $profile) => ProviderChatResultDTO::from($profile))
            ->values();
    }

    /**
     * Search by service type/category.
     *
     * @return Collection<int, ProviderChatResultDTO>
     */
    public function searchByService(
        string $service,
        ?int $cityId = null,
        ?int $categoryHint = null,
    ): Collection {
        $query = $this->buildBaseQuery();

        if ($categoryHint !== null) {
            $query->where('profiles.category_id', $categoryHint);
        } else {
            // Fuzzy match on category names
            $searchTerm = "%{$service}%";
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('categories.name', 'like', $searchTerm)
                    ->orWhere('categories.name_ar', 'like', $searchTerm)
                    ->orWhere('profiles.bio', 'like', $searchTerm);
            });
        }

        if ($cityId !== null) {
            $query->where('profiles.city_id', $cityId);
        }

        $this->rankingService->applySearchRanking($query);

        return $query
            ->with(['user', 'category', 'city', 'subcategories', 'stats', 'approvedReviews'])
            ->get()
            ->map(fn (Profile $profile) => ProviderChatResultDTO::from($profile))
            ->values();
    }

    /**
     * Search for providers matching optional criteria (legacy support).
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
