<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProviderCardResource;
use App\Models\Profile;
use App\Models\UserFavorite;
use App\Services\ProfileVisibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FavoriteController extends BaseApiController
{
    public function __construct(private readonly ProfileVisibilityService $visibilityService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Profile::query()
            ->join('user_favorites', 'user_favorites.profile_id', '=', 'profiles.id')
            ->where('user_favorites.user_id', $user->id)
            ->select('profiles.*')
            ->join('users', 'users.id', '=', 'profiles.user_id')
            ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id');

        $query = $this->visibilityService->applyVisibleQuery($query);
        $query->with(['user', 'stats', 'city', 'category', 'subcategories']);

        $perPage = min(max($request->integer('per_page', 15), 5), 30);
        $paginator = $query->paginate($perPage);

        return $this->paginated($paginator, ProviderCardResource::class);
    }

    public function store(string $providerSlug, Request $request): JsonResponse
    {
        $profile = $this->resolveProfileBySlug($providerSlug);

        if (! $this->visibilityService->isDiscoverable($profile)) {
            return $this->error('هذا الملف الشخصي غير ظاهر حالياً للعملاء.', 422);
        }

        UserFavorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'profile_id' => $profile->id,
        ]);

        return $this->success([], 'تم إضافة المزود إلى المفضلة بنجاح.');
    }

    public function destroy(string $providerSlug, Request $request): JsonResponse
    {
        $profile = $this->resolveProfileBySlug($providerSlug);

        UserFavorite::where('user_id', $request->user()->id)
            ->where('profile_id', $profile->id)
            ->delete();

        return $this->success([], 'تم إزالة المزود من المفضلة بنجاح.');
    }

    private function resolveProfileBySlug(string $providerSlug): Profile
    {
        return Profile::query()
            ->where('slug', $providerSlug)
            ->firstOr(function (): never {
                throw new NotFoundHttpException;
            });
    }
}
