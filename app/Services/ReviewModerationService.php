<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewModerationService
{
    public function approve(
        Review $review,
        ?string $note = null
    ): void {
        $review->update([
            'status' => 'approved',
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
            'moderation_note' => $note,
        ]);
    }

    public function reject(
        Review $review,
        ?string $note = null
    ): void {
        $review->update([
            'status' => 'rejected',
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
            'moderation_note' => $note,
        ]);
    }

    public function markFlagHandled(Review $review): void
    {
        $review->update([
            'flag_handled_by' => Auth::id(),
            'flag_handled_at' => now(),
        ]);
    }

    public function keep(Review $review, ?string $note = null): void
    {
        $this->approve($review, $note);
        $this->markFlagHandled($review);
    }

    public function softDelete(Review $review, ?string $note = null): void
    {
        if ($note !== null) {
            $review->update([
                'moderated_by' => Auth::id(),
                'moderated_at' => now(),
                'moderation_note' => $note,
            ]);
        }

        $review->delete();
    }

    public function restore(Review $review, ?string $note = null): void
    {
        $review->restore();

        $review->update([
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
            'moderation_note' => $note,
        ]);
    }
}
