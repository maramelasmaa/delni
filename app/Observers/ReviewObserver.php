<?php

namespace App\Observers;

use App\Jobs\RecalculateProfileStatsJob;
use App\Models\Review;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

class ReviewObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function created(Review $review): void
    {
        RecalculateProfileStatsJob::dispatch($review->profile_id)->afterCommit();

        $this->activityLog->log(
            actorId: Context::get('actor_id') ?? Auth::id(),
            subject: $review,
            action: 'review_created',
            description: "Review created on profile #{$review->profile_id} by user #{$review->user_id}",
            properties: ['rating' => $review->rating, 'status' => $review->status->value],
        );
    }

    public function updated(Review $review): void
    {
        if ($review->wasChanged('status') || $review->wasChanged('rating') || $review->wasChanged('profile_id')) {
            RecalculateProfileStatsJob::dispatch($review->profile_id)->afterCommit();
        }

        if ($review->wasChanged('status')) {
            $this->activityLog->log(
                actorId: Context::get('actor_id') ?? Auth::id(),
                subject: $review,
                action: 'review_moderated',
                description: "Review #{$review->id} status changed to: {$review->status->value}",
                properties: [
                    'new_status' => $review->status->value,
                    'moderation_note' => $review->moderation_note,
                    'moderated_by' => $review->moderated_by,
                ],
            );
        }

        if ($review->wasChanged('is_flagged') && $review->is_flagged) {
            $this->activityLog->log(
                actorId: Context::get('actor_id') ?? Auth::id(),
                subject: $review,
                action: 'review_flagged',
                description: "Review #{$review->id} flagged on profile #{$review->profile_id}",
                properties: [
                    'flagged_by' => $review->flagged_by,
                    'flagged_reason' => $review->flagged_reason,
                ],
            );
        }

        if ($review->wasChanged('flag_handled_at') && $review->flag_handled_at !== null) {
            $this->activityLog->log(
                actorId: Context::get('actor_id') ?? Auth::id(),
                subject: $review,
                action: 'review_flag_handled',
                description: "Review #{$review->id} flag marked handled",
                properties: [
                    'flag_handled_by' => $review->flag_handled_by,
                    'flag_handled_at' => $review->flag_handled_at?->toDateTimeString(),
                ],
            );
        }
    }

    public function deleted(Review $review): void
    {
        RecalculateProfileStatsJob::dispatch($review->profile_id)->afterCommit();

        $this->activityLog->log(
            actorId: Context::get('actor_id') ?? Auth::id(),
            subject: $review,
            action: 'review_deleted',
            description: "Review #{$review->id} soft-deleted from profile #{$review->profile_id}",
            properties: [],
        );
    }

    public function restored(Review $review): void
    {
        RecalculateProfileStatsJob::dispatch($review->profile_id)->afterCommit();

        $this->activityLog->log(
            actorId: Context::get('actor_id') ?? Auth::id(),
            subject: $review,
            action: 'review_restored',
            description: "Review #{$review->id} restored on profile #{$review->profile_id}",
            properties: [],
        );
    }
}
