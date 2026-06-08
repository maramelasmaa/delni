<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AccountSecurityService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function recordFailedAttempt(string $email): void
    {
        DB::transaction(function () use ($email): void {
            $now = Carbon::now();

            $user = DB::table('users')
                ->where('email', $email)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->select(['id', 'failed_login_attempts'])
                ->first();

            if ($user === null) {
                return;
            }

            $attempts = (int) $user->failed_login_attempts + 1;

            $updates = array_merge(
                ['failed_login_attempts' => $attempts, 'last_failed_login_at' => $now],
                $this->lockoutUpdates($attempts, $now),
            );

            DB::table('users')->where('id', $user->id)->update($updates);

            // Log account lockout when triggered
            if (isset($updates['locked_until'])) {
                $this->activityLog->logSystem(
                    action: 'user_account_locked',
                    description: "User #{$user->id} locked after {$attempts} failed login attempt(s)",
                    properties: [
                        'locked_until' => $updates['locked_until'],
                        'security_flagged' => $updates['security_flagged'] ?? false,
                    ],
                );
            }
        });
    }

    public function recordSuccessfulLogin(User $user): void
    {
        $user->forceFill([
            'failed_login_attempts' => 0,
            'last_failed_login_at' => null,
            'locked_until' => null,
        ])->saveQuietly();
    }

    public function isLocked(User $user): bool
    {
        return $user->locked_until !== null
            && Carbon::parse($user->locked_until)->isFuture();
    }

    /** @return array<string, mixed> */
    private function lockoutUpdates(int $attempts, Carbon $now): array
    {
        if ($attempts >= 50) {
            return [
                'locked_until' => $now->copy()->addHours(72),
                'security_flagged' => true,
            ];
        }

        if ($attempts >= 20) {
            return [
                'locked_until' => $now->copy()->addHours(24),
                'security_flagged' => true,
            ];
        }

        if ($attempts >= 10) {
            return ['locked_until' => $now->copy()->addHour()];
        }

        if ($attempts >= 5) {
            return ['locked_until' => $now->copy()->addMinutes(15)];
        }

        return [];
    }
}
