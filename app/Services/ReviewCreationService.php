<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewCreationService
{
    public function create(User $user, Profile $profile, ?int $rating = null, ?string $comment = null): Review
    {
        return DB::transaction(function () use ($user, $profile, $rating, $comment): Review {
            User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $alreadyReviewed = Review::withTrashed()
                ->where('profile_id', $profile->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyReviewed) {
                throw ValidationException::withMessages([
                    'profile' => __('messages.already_reviewed'),
                ]);
            }

            $dailyCount = Review::query()
                ->where('user_id', $user->id)
                ->whereBetween('created_at', [
                    Carbon::today()->startOfDay(),
                    Carbon::today()->endOfDay(),
                ])
                ->lockForUpdate()
                ->count();

            if ($dailyCount >= 10) {
                throw ValidationException::withMessages([
                    'review' => __('messages.public.review_daily_limit_reached'),
                ]);
            }

            try {
                return Review::create([
                    'profile_id' => $profile->id,
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'status' => ReviewStatus::APPROVED,
                    'comment' => $comment,
                ]);
            } catch (QueryException $exception) {
                if ($this->isDuplicateReviewConstraint($exception)) {
                    throw ValidationException::withMessages([
                        'profile' => __('messages.already_reviewed'),
                    ]);
                }

                throw $exception;
            }
        }, attempts: 5);
    }

    private function isDuplicateReviewConstraint(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'reviews_profile_id_user_id_unique')
            || str_contains($message, 'reviews.profile_id, reviews.user_id')
            || str_contains($message, 'reviews_profile_id_user_id');
    }
}
