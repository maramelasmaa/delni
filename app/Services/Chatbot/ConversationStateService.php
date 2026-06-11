<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Cache;

class ConversationStateService
{
    /**
     * @return array<string, mixed>
     */
    public function get(string $sessionId): array
    {
        return Cache::get($this->stateKey($sessionId), $this->emptyState());
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function save(string $sessionId, array $state): void
    {
        $state['updated_at'] = now()->toIso8601String();
        Cache::put($this->stateKey($sessionId), $state, now()->addHours(2));
    }

    public function forget(string $sessionId): void
    {
        Cache::forget($this->stateKey($sessionId));
        Cache::forget($this->messagesKey($sessionId));
    }

    public function rememberMessage(string $sessionId, string $role, string $message): void
    {
        $messages = $this->messages($sessionId);
        $messages[] = [
            'role' => $role,
            'content' => mb_substr($message, 0, 500),
        ];

        Cache::put($this->messagesKey($sessionId), array_slice($messages, -4), now()->addHours(2));
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    public function messages(string $sessionId): array
    {
        return Cache::get($this->messagesKey($sessionId), []);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyState(): array
    {
        return [
            'intent' => null,
            'service_query' => null,
            'city_id' => null,
            'city_name' => null,
            'category_id' => null,
            'subcategory_id' => null,
            'provider_name_query' => null,
            'min_experience_years' => null,
            'pending_fields' => [],
            'last_results_ids' => [],
            'updated_at' => now()->toIso8601String(),
        ];
    }

    private function stateKey(string $sessionId): string
    {
        return 'chatbot:state:'.$sessionId;
    }

    private function messagesKey(string $sessionId): string
    {
        return 'chatbot:messages:'.$sessionId;
    }
}
