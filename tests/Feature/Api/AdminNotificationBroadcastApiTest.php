<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Jobs\BroadcastAppNotificationJob;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminNotificationBroadcastApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_queue_broadcast_notification(): void
    {
        Queue::fake();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/notifications/broadcast', [
                'title' => 'عنوان عربي',
                'body' => 'رسالة عربية للمستخدمين',
                'data' => [
                    'url' => '/provider/test-provider',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        Queue::assertPushed(BroadcastAppNotificationJob::class);
    }

    public function test_non_admin_cannot_queue_broadcast_notification(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/admin/notifications/broadcast', [
                'title' => 'عنوان عربي',
                'body' => 'رسالة عربية للمستخدمين',
                'data' => [
                    'url' => '/provider/test-provider',
                ],
            ])
            ->assertForbidden();

        Queue::assertNothingPushed();
    }

    public function test_guest_cannot_queue_broadcast_notification(): void
    {
        Queue::fake();

        $this->postJson('/api/v1/admin/notifications/broadcast', [
            'title' => 'عنوان عربي',
            'body' => 'رسالة عربية للمستخدمين',
        ])->assertUnauthorized();

        Queue::assertNothingPushed();
    }
}
