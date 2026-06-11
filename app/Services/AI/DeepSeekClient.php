<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekClient
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, array $options = []): ?string
    {
        $response = $this->send($messages, $options);

        return $response === null
            ? null
            : data_get($response, 'choices.0.message.content');
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<string, mixed>|null
     */
    public function send(array $messages, array $options = []): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            $response = Http::timeout(config('deepseek.timeout'))
                ->retry(2, 250)
                ->withToken(config('deepseek.api_key'))
                ->acceptJson()
                ->post(rtrim((string) config('deepseek.base_url'), '/').'/chat/completions', [
                    'model' => $options['model'] ?? config('deepseek.model'),
                    'messages' => $messages,
                    'temperature' => $options['temperature'] ?? config('deepseek.temperature'),
                    'max_tokens' => $options['max_tokens'] ?? config('deepseek.max_tokens'),
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('DeepSeek API request failed', [
                'status' => $response->status(),
                'error_code' => data_get($response->json(), 'error.code'),
            ]);
        } catch (ConnectionException $e) {
            Log::warning('DeepSeek connection failed', [
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('DeepSeek request error', [
                'message' => $e->getMessage(),
                'class' => $e::class,
            ]);
        }

        return null;
    }

    public function isEnabled(): bool
    {
        return (bool) config('deepseek.enabled', false) && filled(config('deepseek.api_key'));
    }
}
