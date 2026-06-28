<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\ReviewStatus;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewModerationService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_flag_decision_creates_notification_for_flagger(): void
    {
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

        $review = Review::factory()->create([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'status' => ReviewStatus::APPROVED,
            'is_flagged' => true,
            'flagged_by' => $flagger->id,
            'flagged_at' => now()->subHour(),
            'flagged_reason' => 'هذا التقييم مضلل.',
        ]);

        $this->actingAs($admin);
        app(ReviewModerationService::class)->rejectFlag($review, 'بعد المراجعة، قررنا إبقاء التقييم لأنه لا يخالف السياسات.');

        $notification = $flagger->notifications()->latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame('review_flag_decision', $notification->data['type']);
        $this->assertSame('rejected', $notification->data['decision']);
        $this->assertSame('بعد المراجعة، قررنا إبقاء التقييم لأنه لا يخالف السياسات.', $notification->data['reason']);
    }

    public function test_review_moderation_creates_notification_for_reviewer(): void
    {
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

        $review = Review::factory()->create([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'status' => ReviewStatus::PENDING,
        ]);

        $this->actingAs($admin);
        app(ReviewModerationService::class)->approve($review, 'تمت الموافقة على تقييمك بعد المراجعة.');

        $notification = $reviewer->notifications()->latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame('review_moderation_decision', $notification->data['type']);
        $this->assertSame('approved', $notification->data['decision']);
        $this->assertSame('تمت الموافقة على تقييمك بعد المراجعة.', $notification->data['reason']);
    }

    public function test_user_can_list_notifications(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $user->notify(new \App\Notifications\ReviewFlagDecisionNotification(
            Review::factory()->create(['flagged_reason' => 'سبب البلاغ']),
            'accepted',
            'تم قبول بلاغك لأن المحتوى مخالف.',
        ));

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.type', 'review_flag_decision')
            ->assertJsonPath('data.0.decision', 'accepted')
            ->assertJsonPath('data.0.reason', 'تم قبول بلاغك لأن المحتوى مخالف.');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $user->notify(new \App\Notifications\ReviewFlagDecisionNotification(
            Review::factory()->create(),
            'accepted',
            'تم قبول البلاغ.',
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

        $user->notify(new \App\Notifications\ReviewFlagDecisionNotification(
            Review::factory()->create(),
            'accepted',
            'تم قبول البلاغ.',
        ));

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);
    }
}
