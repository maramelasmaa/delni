<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ReviewFlagDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Review $review,
        private readonly string $decision,
        private readonly ?string $moderationNote,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $profile = $this->review->profile;
        $decisionText = $this->decision === 'accepted' ? 'تم قبول بلاغك' : 'تم رفض بلاغك';
        $providerName = $profile?->business_name ?: $profile?->user?->name ?: 'هذا المزود';

        return new DatabaseMessage([
            'type' => 'review_flag_decision',
            'title' => $decisionText,
            'body' => $this->moderationNote ?: $decisionText.' على التقييم لدى '.$providerName.'.',
            'decision' => $this->decision,
            'reason' => $this->moderationNote,
            'review_id' => $this->review->id,
            'profile_id' => $this->review->profile_id,
            'profile_slug' => $profile?->slug,
            'flagged_reason' => $this->review->flagged_reason,
            'created_at' => now()->toIso8601String(),
        ]);
    }
}
