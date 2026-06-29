<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ProfileSearchFilters;
use App\Enums\ReviewStatus;
use App\Models\Banner;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProviderType;
use App\Models\Review;
use App\Models\Subcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicFrontendService
{
    public function __construct(
        private readonly ProfileSearchService $searchService,
        private readonly MarketplaceRankingService $rankingService,
        private readonly ProfileVisibilityService $visibilityService,
        private readonly ArabicNormalizationService $normalization,
    ) {}

    /** @return array{data: array<string, mixed>, queryStats: array<string, mixed>} */
    public function homepage(?City $activeCity = null): array
    {
        return $this->inspectQueries(function () use ($activeCity): array {
            $cityId = $activeCity?->id;

            $categoryCounts = $this->profileCountsBy('profiles.category_id', $cityId);
            $cityCounts = $this->profileCountsBy('profiles.city_id', $cityId);

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
                ->applyHomepageFeaturedOnly($this->discoverableProfilesQuery($cityId))
                ->limit(8)
                ->get();

            // Suggested providers: top-ranked discoverable providers NOT already in featured,
            // so cities without featured providers still show real results.
            $featuredIds = $featuredProviders->pluck('id')->all();
            $suggestedQuery = $this->rankingService
                ->applySearchRanking($this->discoverableProfilesQuery($cityId));
            if ($featuredIds !== []) {
                $suggestedQuery->whereNotIn('profiles.id', $featuredIds);
            }
            $suggestedProviders = $suggestedQuery->limit(6)->get();

            $loadedProviders = $this->loadHomepageProviderRelations(
                $featuredProviders->merge($suggestedProviders)->values(),
                $categoryMap
            );

            $loadedFeaturedProviders = $this->reuseLoadedProviders(
                $featuredProviders->values(),
                $loadedProviders
            );

            $loadedSuggestedProviders = $this->reuseLoadedProviders(
                $suggestedProviders->values(),
                $loadedProviders
            );

            $statsCacheKey = $cityId ? "homepage.stats.city.{$cityId}" : 'homepage.stats.global';
            $stats = Cache::flexible($statsCacheKey, [180, 600], function () use ($cityId) {
                $reviewsQuery = Review::where('status', ReviewStatus::APPROVED);
                if ($cityId) {
                    $reviewsQuery->whereHas('profile', fn ($q) => $q->where('city_id', $cityId));
                }

                return [
                    'profiles_count' => (int) $this->discoverableProfilesQuery($cityId)->count(),
                    'reviews_count' => (int) $reviewsQuery->count(),
                    'categories_count' => (int) Category::where('is_active', true)->count(),
                    'cities_count' => (int) City::where('is_active', true)->count(),
                ];
            });

            return [
                'banners' => Banner::active()->get(),
                'categories' => $categories,
                'subcategories' => $subcategories,
                'cities' => City::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                    ->each(fn (City $city) => $city->setAttribute('discoverable_profiles_count', (int) ($cityCounts[$city->id] ?? 0))),
                'providerTypes' => ProviderType::options(),
                'featuredProviders' => $loadedFeaturedProviders,
                'suggestedProviders' => $loadedSuggestedProviders,
                'stats' => $stats,
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
                'sort' => $validated['sort'] ?? null,
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
            $serviceSlugs = is_array($request->query('services'))
                ? $request->query('services')
                : ($request->filled('services') ? explode(',', (string) $request->query('services')) : []);

            if (empty($serviceSlugs) && $request->filled('service')) {
                $serviceSlugs = is_array($request->query('service'))
                    ? $request->query('service')
                    : [$request->query('service')];
            }
            $serviceSlugs = array_filter($serviceSlugs);

            $query = $this->discoverableProfilesQuery()->where('profiles.category_id', $category->id);

            if (! empty($serviceSlugs)) {
                $query->whereExists(function ($sub) use ($serviceSlugs): void {
                    $sub->select(DB::raw(1))
                        ->from('profile_subcategory')
                        ->join('subcategories', 'subcategories.id', '=', 'profile_subcategory.subcategory_id')
                        ->whereColumn('profile_subcategory.profile_id', 'profiles.id')
                        ->whereIn('subcategories.slug', $serviceSlugs);
                });
            }

            $profiles = $this->paginateProfiles(
                $this->rankingService
                    ->applyCategoryRanking(
                        $this->applyArchiveFilters($query, $request)
                    ),
                $request
            );

            $subcategoryCounts = $this->profileCountsBySubcategory();
            $category->load('icon');
            $category->load(['subcategories' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->with('icon')]);
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
                'cities' => $this->activeCities(),
                'providerTypes' => ProviderType::options(),
            ];
        });
    }

    public function subcategory(Subcategory $subcategory, Request $request): array
    {
        return $this->inspectQueries(function () use ($subcategory, $request): array {
            $subcategoryCounts = $this->profileCountsBySubcategory();
            $categoryCounts = $this->profileCountsBy('profiles.category_id');

            $subcategory->load([
                'icon',
                'category' => fn ($query) => $query->with([
                    'subcategories' => fn ($query) => $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->with('icon'),
                ]),
            ]);

            $subcategory->category?->setAttribute(
                'discoverable_profiles_count',
                (int) ($categoryCounts[$subcategory->category_id] ?? 0)
            );

            $subcategory->category?->subcategories->each(
                fn (Subcategory $sibling) => $sibling->setAttribute(
                    'discoverable_profiles_count',
                    (int) ($subcategoryCounts[$sibling->id] ?? 0)
                )
            );

            $serviceSlugs = is_array($request->query('services'))
                ? $request->query('services')
                : ($request->filled('services') ? explode(',', (string) $request->query('services')) : []);

            if (empty($serviceSlugs) && $request->filled('service')) {
                $serviceSlugs = is_array($request->query('service'))
                    ? $request->query('service')
                    : [$request->query('service')];
            }
            $serviceSlugs = array_filter($serviceSlugs);

            $subcategoryFilter = function ($query) use ($subcategory, $serviceSlugs): void {
                $query->select(DB::raw(1))
                    ->from('profile_subcategory')
                    ->whereColumn('profile_subcategory.profile_id', 'profiles.id');

                if (! empty($serviceSlugs)) {
                    $query->join('subcategories', 'subcategories.id', '=', 'profile_subcategory.subcategory_id')
                        ->whereIn('subcategories.slug', $serviceSlugs);
                } else {
                    $query->where('profile_subcategory.subcategory_id', $subcategory->id);
                }
            };

            $profiles = $this->paginateProfiles(
                $this->rankingService
                    ->applySubcategoryRanking(
                        $this->applyArchiveFilters(
                            $this->discoverableProfilesQuery()->whereExists($subcategoryFilter),
                            $request
                        )
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
                'cities' => $this->activeCities(),
                'providerTypes' => ProviderType::options(),
            ];
        });
    }

    public function city(City $city, Request $request): array
    {
        return $this->inspectQueries(function () use ($city, $request): array {

            $query = $this->discoverableProfilesQuery()->where('profiles.city_id', $city->id);

            if ($categoryId = $this->categoryFilterId($request)) {
                $query->where('profiles.category_id', $categoryId);
            }

            if ($request->boolean('remote') || $request->query('remote') === '1') {
                $query->where('profiles.offers_remote_work', true);
            }

            $profiles = $this->paginateProfiles(
                $this->rankingService->applySearchRanking($query),
                $request
            );

            return [
                'city' => $city,
                'profiles' => $profiles,
                'providerCount' => $profiles->total(),
                'categories' => $this->activeCategories(),
            ];
        });
    }

    public function topRated(Request $request): array
    {
        return $this->inspectQueries(function () use ($request): array {
            $query = $this->discoverableProfilesQuery();

            if ($cityId = $this->cityFilterId($request)) {
                $query->where('profiles.city_id', $cityId);
            }

            if ($categoryId = $this->categoryFilterId($request)) {
                $query->where('profiles.category_id', $categoryId);
            }

            if ($request->boolean('remote') || $request->query('remote') === '1') {
                $query->where('profiles.offers_remote_work', true);
            }

            $this->applyKeywordFilter($query, (string) $request->query('keyword', ''));

            $profiles = $this->paginateProfiles(
                $this->rankingService
                    ->applyTopRatedEligibility($query),
                $request,
                ['stats', 'city', 'category', 'subcategories']
            );

            return [
                'profiles' => $profiles,
                'providerCount' => $profiles->total(),
                'categories' => $this->activeCategories(),
                'cities' => $this->activeCities(),
                'filters' => $request->only(['city', 'city_id', 'category', 'category_id', 'keyword']),
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
                ->each(function (Category $category) use ($categoryCounts, $subcategoryCounts) {
                    $category->setAttribute('discoverable_profiles_count', (int) ($categoryCounts[$category->id] ?? 0));
                    $category->subcategories->each(fn (Subcategory $subcategory) => $subcategory->setAttribute(
                        'discoverable_profiles_count',
                        (int) ($subcategoryCounts[$subcategory->id] ?? 0)
                    ));
                });

            return [
                'categories' => $categories,
            ];
        });
    }

    public function provider(Profile $profile): array
    {
        return $this->inspectQueries(function () use ($profile): array {
            // Check visibility before loading heavy relations — avoid wasted DB work on hidden profiles
            abort_unless($this->visibilityService->isDiscoverable($profile), 404);

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
                // Only embed the first page of reviews in the detail payload — the full
                // list is served by the dedicated paginated GET /providers/{slug}/reviews
                // endpoint. Loading every approved review here is unbounded memory + payload
                // for popular providers.
                'approvedReviews' => fn ($query) => $query->orderByDesc('created_at')->limit(10),
                'approvedReviews.user',
            ]);

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
    private function discoverableProfilesQuery(?int $cityId = null): Builder
    {
        $query = Profile::query()
            ->without('user')
            ->select('profiles.*')
            ->join('users', 'users.id', '=', 'profiles.user_id')
            ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id');

        if ($cityId) {
            $query->where('profiles.city_id', $cityId);
        }

        // Apply visibility conditions from ProfileVisibilityService (single source of truth)
        return $this->visibilityService->applyVisibleQuery($query);
    }

    /** @param Builder<Profile> $query */
    private function applyArchiveFilters(Builder $query, Request $request): Builder
    {
        if ($cityId = $this->cityFilterId($request)) {
            $query->where('profiles.city_id', $cityId);
        }

        if ($request->boolean('remote') || $request->query('remote') === '1') {
            $query->where('profiles.offers_remote_work', true);
        }

        $this->applyKeywordFilter($query, (string) $request->query('keyword', ''));

        return $query;
    }

    private function cityFilterId(Request $request): ?int
    {
        if ($request->filled('city_id')) {
            return $request->integer('city_id');
        }

        if (! $request->filled('city')) {
            return null;
        }

        $id = City::query()
            ->where('slug', (string) $request->query('city'))
            ->where('is_active', true)
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function categoryFilterId(Request $request): ?int
    {
        if ($request->filled('category_id')) {
            return $request->integer('category_id');
        }

        if (! $request->filled('category')) {
            return null;
        }

        $id = Category::query()
            ->where('slug', (string) $request->query('category'))
            ->where('is_active', true)
            ->value('id');

        return $id ? (int) $id : null;
    }

    /** @param Builder<Profile> $query */
    private function applyKeywordFilter(Builder $query, string $keyword): void
    {
        $keyword = trim(strip_tags($keyword));

        if (mb_strlen($keyword) < 2) {
            return;
        }

        $normalizedKeyword = $this->normalization->normalize($keyword);
        $likeKeyword = '%'.addcslashes($normalizedKeyword, '%_\\').'%';

        $query->where(function (Builder $query) use ($likeKeyword): void {
            $query
                ->where('profiles.search_business_name', 'like', $likeKeyword)
                ->orWhere('profiles.search_bio', 'like', $likeKeyword);
        });
    }

    /** @param Builder<Profile> $query */
    /** @param array<int, string> $relations */
    private function paginateProfiles(
        Builder $query,
        Request $request,
        array $relations = ['stats', 'city', 'category', 'subcategories']
    ): LengthAwarePaginator {
        $this->applyArchiveSort($query, (string) $request->query('sort', ''));

        $profiles = $query->paginate(
            perPage: min(max($request->integer('per_page', 15), 5), 50),
            page: max($request->integer('page', 1), 1)
        )->withQueryString();

        // Always eager-load a lean user (id+name only) — the base query uses
        // ->without('user'), but ProviderCardResource falls back to $this->user->name
        // when business_name is empty, which would otherwise lazy-load one users row
        // per card (N+1). Selecting just id,name keeps the payload minimal.
        $relations = array_values(array_unique([...$relations, 'user:id,name']));

        $profiles->getCollection()->loadMissing($relations);

        return $profiles;
    }

    /** @param Builder<Profile> $query */
    private function applyArchiveSort(Builder $query, string $sort): void
    {
        match ($sort) {
            'rating' => $query
                ->reorder('profile_stats.rating_avg', 'desc')
                ->orderByDesc('profile_stats.reviews_count')
                ->orderByDesc('profiles.id'),
            'reviews' => $query
                ->reorder('profile_stats.reviews_count', 'desc')
                ->orderByDesc('profile_stats.rating_avg')
                ->orderByDesc('profiles.id'),
            'newest' => $query
                ->reorder('profiles.created_at', 'desc')
                ->orderByDesc('profiles.id'),
            default => null,
        };
    }

    /**
     * @param  EloquentCollection<int, Profile>  $profiles
     */
    private function loadHomepageProviderRelations(EloquentCollection $profiles, mixed $categoryMap): EloquentCollection
    {
        $profiles->loadMissing(['stats', 'city', 'subcategories', 'user:id,name']);
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
    private function profileCountsBy(string $column, ?int $cityId = null): array
    {
        $suffix = $cityId ? ".city.{$cityId}" : '.global';
        $key = 'frontend.profile_counts.'.str_replace('.', '_', $column).$suffix;

        return Cache::flexible($key, [60, 300], fn () => $this->discoverableProfilesQuery($cityId)
            ->select($column, DB::raw('COUNT(*) as aggregate'))
            ->groupBy($column)
            ->pluck('aggregate', $column)
            ->map(fn ($count) => (int) $count)
            ->all()
        );
    }

    /** @return array<int, int> */
    private function profileCountsBySubcategory(?int $cityId = null): array
    {
        $suffix = $cityId ? ".city.{$cityId}" : '.global';

        return Cache::flexible('frontend.profile_counts.subcategory_id'.$suffix, [60, 300], fn () => $this->discoverableProfilesQuery($cityId)
            ->join('profile_subcategory', 'profile_subcategory.profile_id', '=', 'profiles.id')
            ->select('profile_subcategory.subcategory_id', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('profile_subcategory.subcategory_id')
            ->pluck('aggregate', 'profile_subcategory.subcategory_id')
            ->map(fn ($count) => (int) $count)
            ->all()
        );
    }

    private function activeCategories(): EloquentCollection
    {
        return once(function () {
            return Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
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
        return once(function () {
            return City::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /** @return array{data: array<string, mixed>, queryStats: array<string, mixed>} */
    private function inspectQueries(callable $callback): array
    {
        $debug = (bool) config('app.debug');

        if ($debug) {
            DB::flushQueryLog();
            DB::enableQueryLog();
        }

        $data = $callback();

        if ($debug) {
            $queries = DB::getQueryLog();
            DB::disableQueryLog();
        } else {
            $queries = [];
        }

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
