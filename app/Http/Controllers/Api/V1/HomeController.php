<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CityResource;
use App\Http\Resources\HomeResource;
use App\Models\City;
use App\Models\ContactInfo;
use App\Models\Profile;
use App\Models\ProviderType;
use App\Services\ProfileVisibilityService;
use App\Services\PublicFrontendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends BaseApiController
{
    public function __construct(private readonly PublicFrontendService $frontendService) {}

    public function home(Request $request): JsonResponse
    {
        $activeCity = null;

        if ($request->filled('city')) {
            $activeCity = City::query()
                ->where('slug', (string) $request->query('city'))
                ->where('is_active', true)
                ->first();
        }

        $payload = $this->frontendService->homepage($activeCity);

        return $this->success(new HomeResource($payload['data']));
    }

    public function cities(Request $request): JsonResponse
    {
        // Obtain city counts from base query visibility
        $cities = City::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Calculate discoverable profile counts dynamically
        $cityCounts = Cache::flexible('api.city_profile_counts', [60, 300], function () {
            $visibilityService = app(ProfileVisibilityService::class);
            $query = Profile::query()
                ->join('users', 'users.id', '=', 'profiles.user_id');

            $query = $visibilityService->applyVisibleQuery($query);

            return $query->select('profiles.city_id', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('profiles.city_id')
                ->pluck('aggregate', 'profiles.city_id')
                ->map(fn ($c) => (int) $c)
                ->all();
        });

        $cities->each(fn (City $city) => $city->setAttribute('discoverable_profiles_count', (int) ($cityCounts[$city->id] ?? 0)));

        return $this->success(CityResource::collection($cities));
    }

    public function providerTypes(Request $request): JsonResponse
    {
        $types = ProviderType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success($types->map(fn (ProviderType $type) => [
            'code' => $type->code,
            'name' => $type->localized_name,
        ]));
    }

    public function contact(): JsonResponse
    {
        $contactInfo = ContactInfo::first();

        return $this->success([
            'whatsapp' => $contactInfo?->whatsapp,
            'phone' => $contactInfo?->phone,
            'email' => $contactInfo?->email,
            'facebook' => $contactInfo?->facebook,
            'address' => $contactInfo?->address,
        ]);
    }
}
