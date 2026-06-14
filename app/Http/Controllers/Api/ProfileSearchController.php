<?php

namespace App\Http\Controllers\Api;

use App\Data\ProfileSearchFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchProfilesRequest;
use App\Models\Profile;
use App\Services\ProfileSearchService;
use Illuminate\Http\JsonResponse;

class ProfileSearchController extends Controller
{
    public function __construct(private ProfileSearchService $searchService) {}

    /**
     * Search for discoverable profiles.
     *
     * Query parameters:
     * - city_id: Filter by city
     * - category_id: Filter by category
     * - subcategory_id: Filter by subcategory
     * - provider_type: Filter by provider type
     * - remote: Set to 1 to show remote-capable providers
     * - keyword: Search in business_name and bio
     * - page: Pagination page (default: 1)
     * - per_page: Results per page (default: 15, max: 100)
     */
    public function search(SearchProfilesRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $data = [
            'page' => (int) ($validated['page'] ?? 1),
            'per_page' => (int) ($validated['per_page'] ?? 15),
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
        if (array_key_exists('keyword', $validated)) {
            $data['keyword'] = $validated['keyword'];
        }

        $filters = ProfileSearchFilters::fromArray($data);

        $results = $this->searchService->search($filters);

        return response()->json([
            'data' => collect($results->items())->map(fn (Profile $profile): array => $this->serializeProfile($profile))->values(),
            'pagination' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem(),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function serializeProfile(Profile $profile): array
    {
        return [
            'id' => $profile->id,
            'slug' => $profile->slug,
            'business_name' => $profile->business_name,
            'type' => $profile->type,
            'provider_type' => $profile->provider_type,
            'bio' => $profile->bio,
            'offers_remote_work' => $profile->offers_remote_work,
            'city_id' => $profile->city_id,
            'category_id' => $profile->category_id,
            'phone' => $profile->phone,
            'whatsapp' => $profile->whatsapp,
            'logo' => $profile->logo,
            'cover_image' => $profile->cover_image,
            'experience_years' => $profile->experience_years,
            'is_complete' => $profile->is_complete,
            'stats' => $profile->stats === null ? null : [
                'profile_id' => $profile->stats->profile_id,
                'rating_avg' => $profile->getAttribute('approved_reviews_count') > 0
                    ? round((float) $profile->getAttribute('approved_reviews_avg_rating'), 2)
                    : 0.0,
                'reviews_count' => (int) ($profile->getAttribute('approved_reviews_count') ?? 0),
                'is_top_rated' => $profile->stats->is_top_rated,
                'is_featured' => $profile->stats->is_featured,
                'featured_until' => $profile->stats->featured_until?->toDateString(),
            ],
            'city' => $profile->city === null ? null : [
                'id' => $profile->city->id,
                'name' => $profile->city->name,
                'slug' => $profile->city->slug,
            ],
            'category' => $profile->category === null ? null : [
                'id' => $profile->category->id,
                'name' => $profile->category->name,
                'slug' => $profile->category->slug,
            ],
        ];
    }
}
