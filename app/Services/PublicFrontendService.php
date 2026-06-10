<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ProfileSearchFilters;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProviderType;
use App\Models\Subcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicFrontendService
{
    public function __construct(
        private readonly ProfileSearchService $searchService,
        private readonly MarketplaceRankingService $rankingService,
        private readonly ProfileVisibilityService $visibilityService,
    ) {}

    /** @return array{data: array<string, mixed>, queryStats: array<string, mixed>} */
    public function homepage(): array
    {
        return $this->inspectQueries(function (): array {
            $categoryCounts = $this->profileCountsBy('profiles.category_id');
            $cityCounts = $this->profileCountsBy('profiles.city_id');

            $categories = Category::query()
                ->where('is_active', true)
                ->with(['icon', 'subcategories' => fn ($query) => $query->where('is_active', true)->with('icon')])
                ->orderBy('sort_order')
                ->get()
                ->each(fn (Category $category) => $category->setAttribute('discoverable_profiles_count', (int) ($categoryCounts[$category->id] ?? 0)));
            $categoryMap = $categories->keyBy('id');

            $subcategories = Subcategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->each(fn (Subcategory $subcategory) => $subcategory->setRelation('category', $categoryMap->get($subcategory->category_id)));

            $featuredProviders = $this->rankingService
                ->applyHomepageRanking($this->discoverableProfilesQuery())
                ->get();

            $loadedProviders = $this->loadHomepageProviderRelations(
                $featuredProviders->values(),
                $categoryMap
            );

            return [
                'categories' => $categories,
                'subcategories' => $subcategories,
                'cities' => City::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->each(fn (City $city) => $city->setAttribute('discoverable_profiles_count', (int) ($cityCounts[$city->id] ?? 0))),
                'featuredProviders' => $this->reuseLoadedProviders($featuredProviders, $loadedProviders),
            ];
        });
    }

    /** @param array<string, mixed> $validated */
    public function search(array $validated): array
    {
        return $this->inspectQueries(function () use ($validated): array {
            $filters = ProfileSearchFilters::fromArray([
                'city_id' => $validated['city_id'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'subcategory_id' => $validated['subcategory_id'] ?? null,
                'provider_type' => $validated['provider_type'] ?? null,
                'remote' => $validated['remote'] ?? false,
                'keyword' => $validated['keyword'] ?? null,
                'page' => (int) ($validated['page'] ?? 1),
                'per_page' => (int) ($validated['per_page'] ?? 15),
            ]);

            $profiles = $this->searchService->search($filters);
            $profiles->getCollection()->loadMissing(['stats', 'city', 'category', 'subcategories']);
            $categories = $this->activeCategories();

            return [
                'profiles' => $profiles,
                'categories' => $categories,
                'subcategories' => $this->activeSubcategories($categories),
                'cities' => $this->activeCities(),
                'providerTypes' => ProviderType::options(),
                'filters' => $validated,
            ];
        });
    }

    public function category(Category $category, Request $request): array
    {
        return $this->inspectQueries(function () use ($category, $request): array {
            $profiles = $this->paginateProfiles(
                $this->rankingService
                    ->applyCategoryRanking($this->discoverableProfilesQuery()->where('profiles.category_id', $category->id)),
                $request
            );

            $subcategoryCounts = $this->profileCountsBySubcategory();
            $category->load('icon');
            $category->load(['subcategories' => fn ($query) => $query->where('is_active', true)->with('icon')]);
            $category->subcategories->each(
                fn (Subcategory $subcategory) => $subcategory->setAttribute(
                    'discoverable_profiles_count',
                    (int) ($subcategoryCounts[$subcategory->id] ?? 0)
                )
            );

            return [
                'category' => $category,
                'profiles' => $profiles,
                'providerCount' => $profiles->total(),
            ];
        });
    }

    public function subcategory(Subcategory $subcategory, Request $request): array
    {
        return $this->inspectQueries(function () use ($subcategory, $request): array {
            $subcategory->load('category', 'icon');

            $profiles = $this->paginateProfiles(
                $this->rankingService
                    ->applySubcategoryRanking(
                        $this->discoverableProfilesQuery()
                            ->whereExists(function ($query) use ($subcategory): void {
                                $query->select(DB::raw(1))
                                    ->from('profile_subcategory')
                                    ->whereColumn('profile_subcategory.profile_id', 'profiles.id')
                                    ->where('profile_subcategory.subcategory_id', $subcategory->id);
                            })
                    ),
                $request,
                ['stats', 'city', 'subcategories']
            );
            $profiles->getCollection()->each(
                fn (Profile $profile) => $profile->setRelation('category', $subcategory->category)
            );

            return [
                'subcategory' => $subcategory,
                'profiles' => $profiles,
                'providerCount' => $profiles->total(),
            ];
        });
    }

    public function city(City $city, Request $request): array
    {
        return $this->inspectQueries(function () use ($city, $request): array {

            $profiles = $this->paginateProfiles(
                $this->rankingService
                    ->applySearchRanking($this->discoverableProfilesQuery()->where('profiles.city_id', $city->id)),
                $request
            );

            return [
                'city' => $city,
                'profiles' => $profiles,
                'providerCount' => $profiles->total(),
            ];
        });
    }

    public function topRated(Request $request): array
    {
        return $this->inspectQueries(function () use ($request): array {
            $query = $this->discoverableProfilesQuery();

            if ($request->filled('city_id')) {
                $query->where('profiles.city_id', $request->integer('city_id'));
            }

            if ($request->filled('category_id')) {
                $query->where('profiles.category_id', $request->integer('category_id'));
            }

            $profiles = $this->paginateProfiles(
                $this->rankingService
                    ->applyTopRatedEligibility($query),
                $request,
                ['stats', 'city', 'category']
            );

            return [
                'profiles' => $profiles,
                'providerCount' => $profiles->total(),
                'categories' => $this->activeCategories(),
                'cities' => $this->activeCities(),
                'filters' => $request->only(['city_id', 'category_id']),
            ];
        });
    }

    public function allCategories(): array
    {
        return $this->inspectQueries(function (): array {
            $categoryCounts = $this->profileCountsBy('profiles.category_id');
            $subcategoryCounts = $this->profileCountsBySubcategory();

            $categories = Category::query()
                ->where('is_active', true)
                ->with(['icon', 'subcategories' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->with('icon')])
                ->orderBy('sort_order')
                ->get()
                ->each(function (Category $category) use ($categoryCounts) {
                    $category->setAttribute('discoverable_profiles_count', (int) ($categoryCounts[$category->id] ?? 0));
                })
                ->each(function (Category $category) use ($subcategoryCounts) {
                    $category->subcategories->each(function (Subcategory $subcategory) use ($subcategoryCounts) {
                        $subcategory->setAttribute('discoverable_profiles_count', (int) ($subcategoryCounts[$subcategory->id] ?? 0));
                    });
                });

            return [
                'categories' => $categories,
            ];
        });
    }

    public function provider(Profile $profile): array
    {
        return $this->inspectQueries(function () use ($profile): array {
            // Load all required relations upfront with optimized queries
            $profile->load([
                'user',
                'stats',
                'city',
                'category',
                'subcategories',
                'activeLinks',
                'credentials',
                'portfolioItems' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'portfolioItems.images' => fn ($query) => $query->orderBy('sort_order'),
                'approvedReviews' => fn ($query) => $query->orderByDesc('created_at'),
                'approvedReviews.user',
            ]);

            abort_unless($this->visibilityService->isDiscoverable($profile), 404);

            return [
                'profile' => $profile,
                'links' => $profile->activeLinks,
                'credentials' => $profile->credentials,
                'portfolioItems' => $profile->portfolioItems,
                'reviews' => $profile->approvedReviews,
                'isDiscoverable' => true,
            ];
        });
    }

    /** @return Builder<Profile> */
    private function discoverableProfilesQuery(): Builder
    {
        $query = Profile::query()
            ->without('user')
            ->select('profiles.*')
            ->join('users', 'users.id', '=', 'profiles.user_id')
            ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id');

        // Apply visibility conditions from ProfileVisibilityService (single source of truth)
        return $this->visibilityService->applyVisibleQuery($query);
    }

    /** @param Builder<Profile> $query */
    /** @param array<int, string> $relations */
    private function paginateProfiles(
        Builder $query,
        Request $request,
        array $relations = ['stats', 'city', 'category', 'subcategories']
    ): LengthAwarePaginator {
        $profiles = $query->paginate(
            perPage: min(max($request->integer('per_page', 15), 5), 50),
            page: max($request->integer('page', 1), 1)
        )->withQueryString();

        $profiles->getCollection()->loadMissing($relations);

        return $profiles;
    }

    /** @param EloquentCollection<int, Profile> $profiles */
    private function loadProviderCardRelations(EloquentCollection $profiles): EloquentCollection
    {
        return $profiles->loadMissing(['stats', 'city', 'category', 'subcategories']);
    }

    /**
     * @param  EloquentCollection<int, Profile>  $profiles
     */
    private function loadHomepageProviderRelations(EloquentCollection $profiles, mixed $categoryMap): EloquentCollection
    {
        $profiles->loadMissing(['stats', 'city', 'subcategories']);
        $profiles->each(fn (Profile $profile) => $profile->setRelation('category', $categoryMap->get($profile->category_id)));

        return $profiles;
    }

    /**
     * @param  EloquentCollection<int, Profile>  $profiles
     * @param  EloquentCollection<int, Profile>  $loadedProviders
     * @return EloquentCollection<int, Profile>
     */
    private function reuseLoadedProviders(EloquentCollection $profiles, EloquentCollection $loadedProviders): EloquentCollection
    {
        return $profiles
            ->map(fn (Profile $profile) => $loadedProviders->firstWhere('id', $profile->id) ?? $profile)
            ->values();
    }

    /** @return array<int, int> */
    private function profileCountsBy(string $column): array
    {
        return $this->discoverableProfilesQuery()
            ->select($column, DB::raw('COUNT(*) as aggregate'))
            ->groupBy($column)
            ->pluck('aggregate', $column)
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /** @return array<int, int> */
    private function profileCountsBySubcategory(): array
    {
        return $this->discoverableProfilesQuery()
            ->join('profile_subcategory', 'profile_subcategory.profile_id', '=', 'profiles.id')
            ->select('profile_subcategory.subcategory_id', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('profile_subcategory.subcategory_id')
            ->pluck('aggregate', 'profile_subcategory.subcategory_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private function activeCategories(): EloquentCollection
    {
        return Category::query()->where('is_active', true)->orderBy('sort_order')->get();
    }

    private function activeSubcategories(?EloquentCollection $categories = null): EloquentCollection
    {
        $categories ??= $this->activeCategories();
        $categoryMap = $categories->keyBy('id');

        return Subcategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->each(fn (Subcategory $subcategory) => $subcategory->setRelation('category', $categoryMap->get($subcategory->category_id)));
    }

    private function activeCities(): EloquentCollection
    {
        return City::query()->where('is_active', true)->orderBy('name')->get();
    }

    /** @return array{data: array<string, mixed>, queryStats: array<string, mixed>} */
    private function inspectQueries(callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $data = $callback();
        $queries = DB::getQueryLog();

        DB::disableQueryLog();

        return [
            'data' => $data,
            'queryStats' => $this->queryStats($queries),
        ];
    }

    /** @param array<int, array{query: string, bindings: array<int, mixed>, time: float}> $queries */
    private function queryStats(array $queries): array
    {
        $seen = [];

        foreach ($queries as $query) {
            $sql = preg_replace('/\s+/', ' ', trim($query['query']));
            $seen[$sql] = ($seen[$sql] ?? 0) + 1;
        }

        return [
            'count' => count($queries),
            'duplicates' => array_filter($seen, fn (int $count): bool => $count > 1),
            'queries' => array_map(
                fn (array $query): string => preg_replace('/\s+/', ' ', trim($query['query'])),
                $queries
            ),
        ];
    }
}
