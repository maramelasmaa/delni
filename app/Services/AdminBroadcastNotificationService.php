<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendExpoPushChunkJob;
use App\Models\PushToken;
use App\Models\User;
use App\Notifications\AppBroadcastNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AdminBroadcastNotificationService
{
    private const USER_CHUNK_SIZE = 500;

    private const EXPO_CHUNK_SIZE = 100;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function broadcast(array $payload, ?int $triggeredByUserId = null): void
    {
        $this->storeDatabaseNotifications($payload);
        $this->dispatchPushJobs($payload, $triggeredByUserId);

        Log::info('Admin broadcast notification queued for delivery.', [
            'triggered_by_user_id' => $triggeredByUserId,
            'title' => $payload['title'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function storeDatabaseNotifications(array $payload): void
    {
        User::query()
            ->where('is_active', true)
            ->where('is_suspended', false)
            ->select(['id'])
            ->chunkById(self::USER_CHUNK_SIZE, function (Collection $users) use ($payload): void {
                $users->each(function (User $user) use ($payload): void {
                    $user->notify(new AppBroadcastNotification($payload));
                });
            });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatchPushJobs(array $payload, ?int $triggeredByUserId = null): void
    {
        PushToken::query()
            ->where('provider', 'expo')
            ->where('is_active', true)
            ->select(['id', 'token'])
            ->chunkById(self::EXPO_CHUNK_SIZE, function (Collection $tokens) use ($payload, $triggeredByUserId): void {
                $messages = $tokens
                    ->map(fn (PushToken $pushToken): array => [
                        'to' => $pushToken->token,
                        'title' => $payload['title'],
                        'body' => $payload['body'],
                        'data' => is_array($payload['data'] ?? null) ? $payload['data'] : [],
                        'sound' => 'default',
                    ])
                    ->values()
                    ->all();

                if ($messages !== []) {
                    SendExpoPushChunkJob::dispatch($messages, $triggeredByUserId)->afterCommit();
                }
            });
    }
}
