<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Cache;

/**
 * Conversation state repository.
 *
 * Centralized state management for chatbot conversations.
 * Uses cache with 24-hour TTL for stateful multi-turn conversations.
 *
 * Per §4 (Caching): Single repository prevents scattered cache logic.
 */
class ConversationStateRepository
{
    private const STATE_CACHE_TTL = 86400; // 24 hours
    private const STATE_CACHE_PREFIX = 'chatbot:state:';

    /**
     * Load conversation state from cache.
     *
     * @return array<string, mixed>
     */
    public function load(string $conversationId): array
    {
        return Cache::get(
            $this->cacheKey($conversationId),
            $this->defaultState(),
        );
    }

    /**
     * Save conversation state to cache.
     */
    public function save(string $conversationId, array $state): void
    {
        Cache::put(
            $this->cacheKey($conversationId),
            $state,
            now()->addSeconds(self::STATE_CACHE_TTL),
        );
    }

    /**
     * Clear conversation state.
     */
    public function clear(string $conversationId): void
    {
        Cache::forget($this->cacheKey($conversationId));
    }

    /**
     * Get default state structure.
     *
     * @return array<string, mixed>
     */
    private function defaultState(): array
    {
        return [
            'service_query' => null,
            'city' => null,
            'min_experience_years' => null,
            'last_results_ids' => [],
            'last_intent' => null,
        ];
    }

    /**
     * Build cache key.
     */
    private function cacheKey(string $conversationId): string
    {
        return self::STATE_CACHE_PREFIX.$conversationId;
    }
}
