<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\ReviewStatus;
use App\Jobs\SendExpoPushChunkJob;
use App\Models\Profile;
use App\Models\PushToken;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ReviewFlagDecisionNotification;
use App\Services\ReviewModerationService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_flag_decision_creates_notification_and_push_job_for_flagger(): void
    {
        Queue::fake();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $provider = User::factory()->create();
        $provider->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $provider->id,
            'provider_access_ends_at' => now()->addYear(),
        ]);

        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');

        $flagger = User::factory()->create();
        $flagger->assignRole('user');

        PushToken::query()->create([
            'user_id' => $flagger->id,
            'token' => 'ExponentPushToken[flaggerDecision123]',
            'provider' => 'expo',
            'platform' => 'android',
            'device_name' => 'Pixel 9',
            'is_active' => true,
            'last_seen_at' => now(),
        ]);

        $review = Review::factory()->create([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'status' => ReviewStatus::APPROVED,
            'is_flagged' => true,
            'flagged_by' => $flagger->id,
            'flagged_at' => now()->subHour(),
            'flagged_reason' => 'This review is misleading.',
        ]);

        $this->actingAs($admin);

        app(ReviewModerationService::class)->rejectFlag(
            $review,
            'We reviewed this report and kept the review.',
        );

        $notification = $flagger->notifications()->latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame('review_flag_decision', $notification->data['type']);
        $this->assertSame('rejected', $notification->data['decision']);
        $this->assertSame('We reviewed this report and kept the review.', $notification->data['reason']);

        Queue::assertPushed(SendExpoPushChunkJob::class);
    }

    public function test_user_can_list_notifications(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $user->notify(new ReviewFlagDecisionNotification(
            Review::factory()->create(['flagged_reason' => 'Flag reason']),
            'accepted',
            'Your report was accepted because the content violates policy.',
        ));

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'review_flag_decision')
            ->assertJsonPath('data.0.decision', 'accepted')
            ->assertJsonPath('data.0.reason', 'Your report was accepted because the content violates policy.');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $user->notify(new ReviewFlagDecisionNotification(
            Review::factory()->create(),
            'accepted',
            'The report was accepted.',
        ));

        $notification = $user->notifications()->latest()->firstOrFail();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.id', $notification->id);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_can_fetch_unread_notification_count(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $user->notify(new ReviewFlagDecisionNotification(
            Review::factory()->create(),
            'accepted',
            'The report was accepted.',
        ));

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);
    }
}
