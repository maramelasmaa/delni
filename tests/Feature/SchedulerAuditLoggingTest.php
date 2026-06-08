<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AccountSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulerAuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that lock clearing creates activity logs
     * CRITICAL FIX: Verify audit trail for lock clearing
     */
    public function test_lock_clearing_logs_activity(): void
    {
        // Create user with expired lock
        User::create([
            'name' => 'Locked User',
            'email' => 'locked@example.com',
            'password' => bcrypt('password'),
            'locked_until' => now()->subHour(),
        ]);

        // Run clear-expired-locks command
        $this->artisan('users:clear-expired-locks')->assertSuccessful();

        // Verify activity log created
        $this->assertDatabaseHas(ActivityLog::class, [
            'action' => 'user_locks_cleared',
        ]);

        // Verify lock was cleared
        $this->assertDatabaseHas(User::class, [
            'locked_until' => null,
        ]);
    }

    /**
     * Test user account lockout logging
     * CRITICAL FIX: Verify lockout events are logged
     */
    public function test_account_lockout_logs_activity(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'account@example.com',
            'password' => bcrypt('password'),
        ]);
        $service = app(AccountSecurityService::class);

        // Trigger lockout (50 attempts)
        for ($i = 0; $i < 50; $i++) {
            $service->recordFailedAttempt($user->email);
        }

        // Verify activity log created
        $this->assertDatabaseHas(ActivityLog::class, [
            'action' => 'user_account_locked',
        ]);

        // Verify user is locked
        $this->assertTrue($user->refresh()->locked_until !== null);
    }
}
