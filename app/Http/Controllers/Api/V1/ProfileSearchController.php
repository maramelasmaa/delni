<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\ProfileSearchFilters;
use App\Http\Requests\Search\SearchProfilesRequest;
use App\Http\Resources\ProviderCardResource;
use App\Services\ArabicNormalizationService;
use App\Services\ProfileSearchService;
use App\Services\ProfileVisibilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileSearchController extends BaseApiController
{
    public function __construct(
        private readonly ProfileSearchService $searchService,
        private readonly ArabicNormalizationService $normalization,
        private readonly ProfileVisibilityService $visibilityService,
    ) {}

    public function search(SearchProfilesRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $keyword = $validated['q'] ?? $validated['keyword'] ?? null;
        $perPage = min(max((int) ($validated['per_page'] ?? 15), 5), 30);

        $data = [
            'page' => (int) ($validated['page'] ?? 1),
            'per_page' => $perPage,
            'keyword' => $keyword,
        ];

        if (array_key_exists('city_id', $validated)) {
            $data['city_id'] = (int) $validated['city_id'];
        }
        if (array_key_exists('category_id', $validated)) {
            $data['category_id'] = (int) $validated['category_id'];
        }
        if (array_key_exists('subcategory_id', $validated)) {
            $data['subcategory_id'] = (int) $validated['subcategory_id'];
        }
        if (array_key_exists('provider_type', $validated)) {
            $data['provider_type'] = $validated['provider_type'];
        }
        if (array_key_exists('remote', $validated)) {
            $data['remote'] = (bool) $validated['remote'];
        }

        $sort = $validated['sort'] ?? null;
        $data['sort'] = in_array($sort, ['rating', 'reviews', 'newest'], true) ? $sort : null;

        $filters = ProfileSearchFilters::fromArray($data);

        $results = $this->searchService->search($filters);

        $results->getCollection()->loadMissing(['stats', 'city', 'category', 'subcategories']);

        return $this->paginated($results, ProviderCardResource::class);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $q = strip_tags(trim((string) $request->query('q', '')));

        if (mb_strlen($q) < 2) {
            return $this->success(['suggestions' => []]);
        }

        $normalized = $this->normalization->normalize($q);
        $normLike = '%'.addcslashes($normalized, '%_\\').'%';
        $rawLike = '%'.addcslashes($q, '%_\\').'%';
        $now = Carbon::now();

        // Base visibility query for discoverable providers (reused by FULLTEXT + LIKE paths).
        $providerQuery = fn () => DB::table('profiles')
            ->join('users', 'users.id', '=', 'profiles.user_id')
            ->where('profiles.is_complete', true)
            ->whereNotNull('profiles.provider_access_ends_at')
            ->where('profiles.provider_access_ends_at', '>=', $now)
            ->where('users.is_active', true)
            ->where('users.is_suspended', false)
            ->whereNull('profiles.deleted_at')
            ->whereNull('users.deleted_at');

        // Prefer the composite FULLTEXT index (MATCH ... AGAINST) over a leading-wildcard
        // LIKE, which cannot use any index. Boolean mode with a trailing '*' enables prefix
        // matching on the normalized columns.
        $booleanTerm = collect(preg_split('/\s+/', trim($normalized)) ?: [])
            ->map(fn (string $token): string => '+'.preg_replace('/[+\-*"()~<>@]/', '', $token).'*')
            ->reject(fn (string $token): bool => $token === '+*')
            ->implode(' ');

        $providers = collect();

        if ($booleanTerm !== '') {
            $providers = $providerQuery()
                ->whereFullText(['profiles.search_business_name', 'profiles.search_bio'], $booleanTerm, ['mode' => 'boolean'])
                ->select('profiles.business_name')
                ->orderByRaw('LENGTH(profiles.business_name) ASC')
                ->limit(4)
                ->pluck('business_name');
        }

        // Fallback for tokens shorter than MySQL's FULLTEXT minimum, or when MATCH finds nothing.
        if ($providers->isEmpty()) {
            $providers = $providerQuery()
                ->where('profiles.search_business_name', 'like', $normLike)
                ->select('profiles.business_name')
                ->orderByRaw('LENGTH(profiles.business_name) ASC')
                ->limit(4)
                ->pluck('business_name');
        }

        // Active category names
        $categories = DB::table('categories')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->where('name_ar', 'like', $rawLike)
            ->select('name_ar')
            ->limit(3)
            ->pluck('name_ar');

        // Active subcategory names
        $subcategories = DB::table('subcategories')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->where(function ($sq) use ($normLike, $rawLike): void {
                $sq->where('search_name', 'like', $normLike)
                    ->orWhere('name', 'like', $rawLike);
            })
            ->select('name')
            ->limit(3)
            ->pluck('name');

        $suggestions = collect($providers)
            ->merge($categories)
            ->merge($subcategories)
            ->filter()
            ->unique()
            ->values()
            ->take(6)
            ->all();

        return $this->success(['suggestions' => $suggestions]);
    }
}
