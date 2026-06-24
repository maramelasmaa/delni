<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('scheduler:health-check {--max-heartbeat-age=120 : Maximum scheduler heartbeat age in seconds.} {--max-daily-age=36 : Maximum daily cleanup age in hours.}')]
#[Description('Check scheduler heartbeat and last successful cleanup/precompute runs.')]
class SchedulerHealthCheckCommand extends Command
{
    /** @var array<string, string> */
    private const DAILY_TASKS = [
        'scheduler:placements_expire:last_success_at' => 'placements:expire',
        'scheduler:top_rated:last_success_at' => 'profiles:update-top-rated',
    ];

    public function handle(): int
    {
        $healthy = true;
        $now = now();
        $maxHeartbeatAge = (int) $this->option('max-heartbeat-age');
        $maxDailyAge = (int) $this->option('max-daily-age');

        $heartbeat = $this->cacheDate('scheduler:last_heartbeat_at');

        if ($heartbeat === null || $heartbeat->diffInSeconds($now) > $maxHeartbeatAge) {
            $healthy = false;
            $this->error('Scheduler heartbeat is missing or stale.');
        } else {
            $this->info('Scheduler heartbeat is fresh.');
        }

        foreach (self::DAILY_TASKS as $cacheKey => $label) {
            $lastSuccess = $this->cacheDate($cacheKey);

            if ($lastSuccess === null) {
                $this->warn("{$label} has not recorded a successful run yet.");

                continue;
            }

            if ($lastSuccess->diffInHours($now) > $maxDailyAge) {
                $healthy = false;
                $this->error("{$label} has no recent successful run.");

                continue;
            }

            $this->info("{$label} last succeeded at {$lastSuccess->toDateTimeString()}.");
        }

        return $healthy ? self::SUCCESS : self::FAILURE;
    }

    private function cacheDate(string $key): ?Carbon
    {
        $value = Cache::get($key);

        if (! is_string($value) || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}
