<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExpoPushNotificationService
{
    private const SEND_ENDPOINT = 'https://exp.host/--/api/v2/push/send';

    public function __construct(
        private readonly PushTokenService $pushTokenService,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $messages
     */
    public function sendMessages(array $messages, ?int $triggeredByUserId = null): void
    {
        if ($messages === []) {
            return;
        }

        $response = Http::asJson()
            ->acceptJson()
            ->timeout(15)
            ->connectTimeout(5)
            ->retry(3, 500, fn (Throwable $exception): bool => $exception instanceof ConnectionException)
            ->post(self::SEND_ENDPOINT, $messages);

        $response->throw();

        $tickets = $response->json('data', []);
        $invalidTokens = [];

        foreach ($tickets as $index => $ticket) {
            if (! is_array($ticket)) {
                continue;
            }

            $message = $messages[$index] ?? null;
            $token = is_array($message) ? ($message['to'] ?? null) : null;

            if (! is_string($token) || $token === '') {
                continue;
            }

            if (($ticket['status'] ?? null) === 'ok') {
                continue;
            }

            $details = is_array($ticket['details'] ?? null) ? $ticket['details'] : [];
            $errorCode = $details['error'] ?? null;

            Log::warning('Expo push notification delivery failed.', [
                'token' => $token,
                'triggered_by_user_id' => $triggeredByUserId,
                'ticket' => $ticket,
            ]);

            if ($errorCode === 'DeviceNotRegistered') {
                $invalidTokens[] = $token;
            }
        }

        $this->pushTokenService->deactivateTokens($invalidTokens);

        Log::info('Expo push chunk delivered.', [
            'messages_count' => count($messages),
            'invalid_tokens_count' => count($invalidTokens),
            'triggered_by_user_id' => $triggeredByUserId,
        ]);
    }
}
