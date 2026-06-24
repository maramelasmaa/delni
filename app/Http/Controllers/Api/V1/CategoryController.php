<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProviderCardResource;
use App\Http\Resources\SubcategoryResource;
use App\Models\Category;
use App\Models\City;
use App\Models\Subcategory;
use App\Services\PublicFrontendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseApiController
{
    public function __construct(private readonly PublicFrontendService $frontendService) {}

    public function index(): JsonResponse
    {
        $payload = $this->frontendService->allCategories();

        return $this->success(CategoryResource::collection($payload['data']['categories']));
    }

    public function show(Category $category, Request $request): JsonResponse
    {
        if (! $category->is_active) {
            abort(404);
        }

        if ($request->filled('city')) {
            $city = City::where('slug', $request->query('city'))->where('is_active', true)->first();
            if ($city) {
                $request->merge(['city_id' => $city->id]);
            }
        }

        $category->load(['subcategories' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->with('icon')]);

        $payload = $this->frontendService->category($category, $request);
        $paginator = $payload['data']['profiles'];

        $collection = ProviderCardResource::collection($paginator->getCollection())->resolve();

        return $this->success([
            'category' => new CategoryResource($category),
            'providers' => $collection,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    public function subcategory(Subcategory $subcategory, Request $request): JsonResponse
    {
        if (! $subcategory->is_active || ! $subcategory->category?->is_active) {
            abort(404);
        }

        if ($request->filled('city')) {
            $city = City::where('slug', $request->query('city'))->where('is_active', true)->first();
            if ($city) {
                $request->merge(['city_id' => $city->id]);
            }
        }

        $subcategory->load([
            'icon',
            'category' => fn ($query) => $query->with([
                'subcategories' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with('icon'),
            ]),
        ]);

        $payload = $this->frontendService->subcategory($subcategory, $request);
        $paginator = $payload['data']['profiles'];

        $collection = ProviderCardResource::collection($paginator->getCollection())->resolve();

        $siblings = Subcategory::where('category_id', $subcategory->category_id)
            ->where('is_active', true)
            ->where('id', '!=', $subcategory->id)
            ->orderBy('sort_order')
            ->with('icon')
            ->get();

        return $this->success([
            'subcategory' => new SubcategoryResource($subcategory),
            'providers' => $collection,
            'related_subcategories' => SubcategoryResource::collection($siblings),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ]);
    }
}
