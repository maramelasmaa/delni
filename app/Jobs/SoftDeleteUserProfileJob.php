<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SoftDeleteUserProfileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [5, 30, 60];

    public function __construct(public readonly int $userId) {}

    public function handle(): void
    {
        // withTrashed because the user is already soft-deleted at this point
        $user = User::withTrashed()->find($this->userId);

        $user?->profile?->delete();
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SoftDeleteUserProfileJob failed after all retries', [
            'user_id' => $this->userId,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
