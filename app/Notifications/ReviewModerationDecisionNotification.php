<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ReviewModerationDecisionNotification extends Notification
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
        $providerName = $profile?->business_name ?: $profile?->user?->name ?: 'this provider';
        $decisionText = $this->decision === 'approved' ? 'تم قبول تقييمك' : 'تم رفض تقييمك';

        return new DatabaseMessage([
            'type' => 'review_moderation_decision',
            'title' => $decisionText,
            'body' => $this->moderationNote ?: $decisionText.' لدى '.$providerName.'.',
            'decision' => $this->decision,
            'reason' => $this->moderationNote,
            'review_id' => $this->review->id,
            'profile_id' => $this->review->profile_id,
            'profile_slug' => $profile?->slug,
            'created_at' => now()->toIso8601String(),
        ]);
    }
}
