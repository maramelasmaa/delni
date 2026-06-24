<?php

namespace App\Jobs;

use App\Models\Profile;
use App\Services\ProfileStatsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ShouldBeUnique prevents duplicate recalculations from burst events
 * (e.g., bulk moderation triggering many review updates in quick succession).
 */
class RecalculateProfileStatsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [5, 15, 30];

    public function __construct(public readonly int $profileId) {}

    public function uniqueId(): int
    {
        return $this->profileId;
    }

    public function handle(ProfileStatsService $statsService): void
    {
        $profile = Profile::find($this->profileId);

        if ($profile) {
            $statsService->recalculate($profile);
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('RecalculateProfileStatsJob failed after all retries', [
            'profile_id' => $this->profileId,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
