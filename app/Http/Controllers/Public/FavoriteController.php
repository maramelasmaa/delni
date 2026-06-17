<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\UserFavorite;
use App\Services\ProfileVisibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function __construct(private readonly ProfileVisibilityService $visibilityService) {}

    public function index(Request $request): View
    {
        $favorites = collect();

        if ($request->user()) {
            $query = Profile::query()
                ->join('user_favorites', 'user_favorites.profile_id', '=', 'profiles.id')
                ->where('user_favorites.user_id', $request->user()->id)
                ->select('profiles.*')
                ->withPublicReviewAggregates()
                ->with(['city', 'category', 'subcategories'])
                ->join('users', 'users.id', '=', 'profiles.user_id')
                ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id');

            $favorites = $this->visibilityService
                ->applyVisibleQuery($query)
                ->paginate(20)
                ->withQueryString();
        }

        return view('public.favorites', [
            'favorites' => $favorites,
        ]);
    }

    public function toggle(Request $request, Profile $profile): JsonResponse
    {
        abort_unless($this->visibilityService->isDiscoverable($profile), 404);

        $user = $request->user();

        $existing = UserFavorite::where('user_id', $user->id)
            ->where('profile_id', $profile->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorited = false;
        } else {
            UserFavorite::create([
                'user_id' => $user->id,
                'profile_id' => $profile->id,
            ]);
            $isFavorited = true;
        }

        return response()->json([
            'favorited' => $isFavorited,
        ]);
    }
}
