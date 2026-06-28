<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReviewStatus;
use App\Http\Requests\Review\CreateReviewRequest;
use App\Http\Requests\Review\FlagReviewRequest;
use App\Http\Resources\ProviderCardResource;
use App\Http\Resources\ProviderDetailResource;
use App\Http\Resources\ReviewResource;
use App\Models\City;
use App\Models\Profile;
use App\Models\Review;
use App\Services\ProfileVisibilityService;
use App\Services\PublicFrontendService;
use App\Services\ReviewCreationService;
use App\Services\ReviewModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends BaseApiController
{
    public function __construct(
        private readonly PublicFrontendService $frontendService,
        private readonly ProfileVisibilityService $visibilityService,
    ) {}

    public function show(Profile $profile, Request $request): JsonResponse
    {
        $this->frontendService->provider($profile);

        $profile->loadMissing([
            'user',
            'stats',
            'city',
            'category',
            'subcategories',
            'activeLinks',
            'credentials',
            'portfolioItems.images',
            'approvedReviews.user',
        ]);

        $currentUser = auth('sanctum')->user() ?: $request->user();

        $resource = new ProviderDetailResource($profile);
        $resource->isFavorited = $currentUser
            ? $currentUser->favoriteProfiles()->where('profiles.id', $profile->id)->exists()
            : false;

        [$resource->canReview, $resource->reviewStatusMessage] = $this->resolveReviewEligibility($profile, $currentUser);
        $resource->latestUserReview = $this->resolveLatestUserReview($profile, $currentUser);

        return $this->success($resource);
    }

    /**
     * @return array{bool, string|null}
     */
    private function resolveReviewEligibility(Profile $profile, mixed $currentUser): array
    {
        if (! $currentUser) {
            return [false, 'يرجى تسجيل الدخول أولاً للتقييم.'];
        }

        if ($currentUser->id === $profile->user_id) {
            return [false, 'لا يمكنك تقييم ملفك الشخصي.'];
        }

        if ($currentUser->isProvider() || $currentUser->isAdmin()) {
            return [false, 'حسابك غير مؤهل لكتابة تقييمات.'];
        }

        $alreadyActive = Review::query()
            ->where('profile_id', $profile->id)
            ->where('user_id', $currentUser->id)
            ->whereIn('status', [ReviewStatus::APPROVED->value, ReviewStatus::PENDING->value])
            ->exists();

        return $alreadyActive
            ? [false, 'لقد قمت بتقييم هذا الملف الشخصي مسبقاً.']
            : [true, null];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveLatestUserReview(Profile $profile, mixed $currentUser): ?array
    {
        if (! $currentUser) {
            return null;
        }

        $review = Review::query()
            ->where('profile_id', $profile->id)
            ->where('user_id', $currentUser->id)
            ->latest('created_at')
            ->first();

        if (! $review) {
            return null;
        }

        $status = $review->status instanceof \UnitEnum ? $review->status->value : (string) $review->status;

        return [
            'id' => $review->id,
            'status' => $status,
            'comment' => $review->comment,
            'rating' => $review->rating,
            'moderation_note' => $review->moderation_note,
            'flag_response' => $review->flag_handled_at
                ? ($review->is_flagged ? 'accepted' : 'rejected')
                : null,
            'submitted_at' => $review->created_at?->toIso8601String(),
            'moderated_at' => $review->moderated_at?->toIso8601String(),
        ];
    }

    public function topRated(Request $request): JsonResponse
    {
        if ($request->filled('city')) {
            $city = City::where('slug', $request->query('city'))->where('is_active', true)->first();

            if ($city) {
                $request->merge(['city_id' => $city->id]);
            }
        }

        $payload = $this->frontendService->topRated($request);
        $paginator = $payload['data']['profiles'];

        return $this->paginated($paginator, ProviderCardResource::class);
    }

    public function reviews(Profile $profile, Request $request): JsonResponse
    {
        if (! $this->visibilityService->isDiscoverable($profile)) {
            abort(404);
        }

        $perPage = min(max($request->integer('per_page', 15), 5), 30);

        $paginator = $profile->approvedReviews()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->paginated($paginator, ReviewResource::class);
    }

    public function storeReview(CreateReviewRequest $request, Profile $profile, ReviewCreationService $reviews): JsonResponse
    {
        if (! $this->visibilityService->isDiscoverable($profile)) {
            return $this->error('هذا الملف الشخصي غير ظاهر حالياً للعملاء.', 422);
        }

        $review = $reviews->create(
            user: $request->user(),
            profile: $profile,
            rating: $request->filled('rating') ? $request->integer('rating') : null,
            comment: $request->filled('comment') ? $request->string('comment')->value() : null,
        );

        $review->load('user');

        return $this->success(new ReviewResource($review), 'تم إرسال تقييمك بنجاح.');
    }

    public function flagReview(
        FlagReviewRequest $request,
        Review $review,
        ReviewModerationService $moderation,
    ): JsonResponse
    {
        $moderation->flag(
            $review,
            $request->user()->id,
            $request->string('reason')->value(),
        );

        return $this->success([], 'تم إرسال البلاغ بنجاح.');
    }
}
