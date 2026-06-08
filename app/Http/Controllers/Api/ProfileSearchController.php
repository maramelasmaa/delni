<?php

namespace App\Http\Controllers\Api;

use App\Data\ProfileSearchFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchProfilesRequest;
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
            'data' => $results->items(),
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
}
