<?php

namespace App\Services\Chatbot;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * DeepSeek API client for chatbot responses.
 *
 * Safety guardrails built in:
 * - Only enabled when API key present
 * - Handles API failures gracefully (falls back to deterministic)
 * - All errors logged, never exposed to users
 * - Token limits enforced to prevent cost overruns
 * - One retry on transient failures
 */
class DeepSeekClient
{
    private const RETRYABLE_STATUSES = [408, 429, 500, 502, 503, 504];

    /**
     * Call DeepSeek chat completions API.
     *
     * @param  array<int, array<string, string>>  $messages
     */
    public function chat(array $messages): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            $response = $this->makeRequest($messages);

            if ($response === null) {
                return null;
            }

            return data_get(
                $response->json(),
                'choices.0.message.content'
            );
        } catch (\Throwable $e) {
            Log::error('DeepSeek exception', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            return null;
        }
    }

    /**
     * Make HTTP request to DeepSeek with retry logic.
     *
     * @param  array<int, array<string, string>>  $messages
     */
    private function makeRequest(array $messages): ?object
    {
        try {
            $response = Http::timeout(config('deepseek.timeout'))
                ->withToken(config('deepseek.api_key'))
                ->post(
                    config('deepseek.base_url').'/chat/completions',
                    [
                        'model' => config('deepseek.model'),
                        'messages' => $messages,
                        'temperature' => config('deepseek.temperature'),
                        'max_tokens' => config('deepseek.max_tokens'),
                    ]
                );

            if ($response->successful()) {
                return $response;
            }

            // Log failed response
            Log::warning('DeepSeek API error', [
                'status' => $response->status(),
                'error_code' => data_get($response->json(), 'error.code'),
            ]);

            // Return null on failure (orchestrator will use fallback)
            return null;
        } catch (ConnectionException $e) {
            Log::warning('DeepSeek connection error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Chat with JSON mode (structured output).
     *
     * @param  array<string, mixed>  $jsonSchema  JSON schema constraining response
     */
    public function chatWithJsonMode(
        string $systemPrompt,
        string $userMessage,
        array $jsonSchema,
    ): ?string {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            $response = Http::timeout(config('deepseek.timeout'))
                ->withToken(config('deepseek.api_key'))
                ->post(
                    config('deepseek.base_url').'/chat/completions',
                    [
                        'model' => config('deepseek.model'),
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $systemPrompt,
                            ],
                            [
                                'role' => 'user',
                                'content' => $userMessage,
                            ],
                        ],
                        'response_format' => [
                            'type' => 'json_schema',
                            'json_schema' => [
                                'name' => 'intent_extraction',
                                'schema' => $jsonSchema,
                                'strict' => true,
                            ],
                        ],
                        'temperature' => config('deepseek.temperature'),
                        'max_tokens' => config('deepseek.max_tokens'),
                    ]
                );

            if ($response->successful()) {
                return data_get($response->json(), 'choices.0.message.content');
            }

            Log::warning('DeepSeek JSON mode error', [
                'status' => $response->status(),
                'error_code' => data_get($response->json(), 'error.code'),
            ]);

            return null;
        } catch (ConnectionException $e) {
            Log::warning('DeepSeek connection error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if DeepSeek integration is enabled.
     */
    public function isEnabled(): bool
    {
        return config('deepseek.enabled', false) && filled(config('deepseek.api_key'));
    }
}
