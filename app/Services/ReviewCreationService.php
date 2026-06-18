<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
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

            // Only block if the user has an active (approved or pending) review.
            // Rejected reviews allow the user to submit a new one.
            $alreadyActiveReview = Review::query()
                ->where('profile_id', $profile->id)
                ->where('user_id', $user->id)
                ->whereIn('status', [ReviewStatus::APPROVED->value, ReviewStatus::PENDING->value])
                ->lockForUpdate()
                ->exists();

            if ($alreadyActiveReview) {
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

            return Review::create([
                'profile_id' => $profile->id,
                'user_id' => $user->id,
                'rating' => $rating,
                'status' => ReviewStatus::APPROVED,
                'comment' => $comment,
            ]);
        }, attempts: 5);
    }
}
