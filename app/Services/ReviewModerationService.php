<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class ReviewModerationService
{
    /**
     * Get the current authenticated admin user.
     * Falls back to Auth::id() if user not injected.
     */
    private function getAdminId(): ?int
    {
        return auth('web')->id();
    }

    /**
     * Admin approves a review (general approval, not flag-related).
     * Used for reviews that may have been pending moderation.
     * Sets status to approved and marks as moderated.
     * Stats recalculation triggered via ReviewObserver::updated()
     */
    public function approve(Review $review, ?string $note = null): void
    {
        $this->updateLockedReview($review, [
            'status' => ReviewStatus::APPROVED,
            'moderated_by' => $this->getAdminId(),
            'moderated_at' => now(),
            'moderation_note' => $note,
        ]);
    }

    /**
     * Admin rejects a review (general rejection, not flag-related).
     * Hides the review and removes from ratings.
     * Stats recalculation triggered via ReviewObserver::updated()
     */
    public function reject(Review $review, ?string $note = null): void
    {
        $this->updateLockedReview($review, [
            'status' => ReviewStatus::REJECTED,
            'moderated_by' => $this->getAdminId(),
            'moderated_at' => now(),
            'moderation_note' => $note,
        ]);
    }

    /**
     * Admin ACCEPTS a flag — hides the review.
     * Called when admin agrees the flagged review violates policy.
     * Arabic label: "قبول البلاغ وإخفاء التقييم"
     *
     * Effect:
     *   - status = rejected (hidden from public)
     *   - is_flagged = true (flag accepted)
     *   - flag_handled_by = admin id
     *   - flag_handled_at = now()
     *   - review no longer counts in ratings
     *   - stats recalculated via ReviewObserver::updated()
     */
    public function acceptFlag(Review $review, ?string $note = null): void
    {
        $this->handleFlagDecision($review, [
            'status' => ReviewStatus::REJECTED,
            'is_flagged' => true,
            'flag_handled_by' => $this->getAdminId(),
            'flag_handled_at' => now(),
            'moderated_by' => $this->getAdminId(),
            'moderated_at' => now(),
            'moderation_note' => $note,
        ]);
    }

    /**
     * Admin REJECTS a flag — keeps review public.
     * Called when admin disagrees with the flag and believes review is legitimate.
     * Arabic label: "رفض البلاغ وإبقاء التقييم"
     *
     * Effect:
     *   - status = approved (stays public)
     *   - is_flagged = false (flag rejected, clear the flag)
     *   - flag_handled_by = admin id
     *   - flag_handled_at = now()
     *   - review continues counting in ratings
     *   - review removed from active flag queue
     *   - stats recalculated via ReviewObserver::updated() if status changed
     */
    public function rejectFlag(Review $review, ?string $note = null): void
    {
        $this->handleFlagDecision($review, [
            'status' => ReviewStatus::APPROVED,
            'is_flagged' => false,
            'flag_handled_by' => $this->getAdminId(),
            'flag_handled_at' => now(),
            'moderated_by' => $this->getAdminId(),
            'moderated_at' => now(),
            'moderation_note' => $note,
        ]);
    }

    /**
     * Keep flagged review public (alias for rejectFlag for UI clarity).
     * Deprecated: use rejectFlag() directly for new code.
     */
    public function keep(Review $review, ?string $note = null): void
    {
        $this->rejectFlag($review, $note);
    }

    /**
     * Soft-delete a review.
     * Used for removing reviews from the database (hide permanently).
     * Stats recalculation triggered via ReviewObserver::deleted()
     */
    public function softDelete(Review $review, ?string $note = null): void
    {
        if ($note !== null) {
            $review->update([
                'moderated_by' => $this->getAdminId(),
                'moderated_at' => now(),
                'moderation_note' => $note,
            ]);
        }

        $review->delete();
    }

    /**
     * Restore a soft-deleted review.
     * Returns review to public view and ratings.
     * Stats recalculation triggered via ReviewObserver::restored()
     */
    public function restore(Review $review, ?string $note = null): void
    {
        $review->restore();

        $this->updateLockedReview($review, [
            'status' => ReviewStatus::APPROVED,
            'moderated_by' => $this->getAdminId(),
            'moderated_at' => now(),
            'moderation_note' => $note,
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function handleFlagDecision(Review $review, array $attributes): void
    {
        DB::transaction(function () use ($review, $attributes): void {
            $lockedReview = Review::withTrashed()
                ->whereKey($review->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedReview->flag_handled_at !== null) {
                return;
            }

            $lockedReview->update($attributes);
        }, attempts: 5);
    }

    /** @param array<string, mixed> $attributes */
    private function updateLockedReview(Review $review, array $attributes): void
    {
        DB::transaction(function () use ($review, $attributes): void {
            Review::withTrashed()
                ->whereKey($review->id)
                ->lockForUpdate()
                ->firstOrFail()
                ->update($attributes);
        }, attempts: 5);
    }
}
