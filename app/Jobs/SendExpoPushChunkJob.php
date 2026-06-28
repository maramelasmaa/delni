<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ExpoPushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendExpoPushChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public array $backoff = [10, 60, 300];

    /**
     * @param  array<int, array<string, mixed>>  $messages
     */
    public function __construct(
        public readonly array $messages,
        public readonly ?int $triggeredByUserId = null,
    ) {}

    public function handle(ExpoPushNotificationService $expoPushNotificationService): void
    {
        $expoPushNotificationService->sendMessages($this->messages, $this->triggeredByUserId);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('SendExpoPushChunkJob failed after all retries.', [
            'messages_count' => count($this->messages),
            'triggered_by_user_id' => $this->triggeredByUserId,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
