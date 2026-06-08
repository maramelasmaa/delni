<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ActivityLogService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

#[Signature('users:clear-expired-locks')]
#[Description('Null out locked_until for users whose lock period has expired.')]
class ClearExpiredLocksCommand extends Command
{
    public function handle(): int
    {
        $affected = DB::table('users')
            ->whereNotNull('locked_until')
            ->where('locked_until', '<', now())
            ->update(['locked_until' => null]);

        if ($affected > 0) {
            app(ActivityLogService::class)->logSystem(
                action: 'user_locks_cleared',
                description: "Scheduled lock clearance: {$affected} user lock(s) expired",
                properties: ['affected_count' => $affected],
            );
        }

        $this->info("Cleared expired locks for {$affected} user(s).");

        Cache::put('scheduler:clear_locks:last_success_at', now()->toIso8601String(), now()->addDays(7));
        Cache::put('scheduler:clear_locks:last_affected', $affected, now()->addDays(7));

        return self::SUCCESS;
    }
}
