<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\AdminBroadcastNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class BroadcastAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly array $payload,
        public readonly ?int $triggeredByUserId = null,
    ) {}

    public function handle(AdminBroadcastNotificationService $service): void
    {
        Log::info('BroadcastAppNotificationJob started processing', [
            'triggered_by_user_id' => $this->triggeredByUserId,
        ]);

        $service->broadcast($this->payload, $this->triggeredByUserId);

        Log::info('BroadcastAppNotificationJob completed successfully');
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('BroadcastAppNotificationJob failed after all retries.', [
            'triggered_by_user_id' => $this->triggeredByUserId,
            'payload' => $this->payload,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
