<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Cache;

/**
 * Manages conversation state across messages.
 *
 * State structure:
 * {
 *   "intent": "provider_search",
 *   "city_id": null,
 *   "city_name": null,
 *   "category_id": null,
 *   "subcategory_id": null,
 *   "service_query": null,
 *   "min_experience_years": null,
 *   "pending_field": null,
 *   "last_question": null,
 *   "updated_at": "2026-01-01T00:00:00Z"
 * }
 *
 * Handles:
 * - Multi-turn conversations with pending fields
 * - State persistence via cache
 * - Automatic state clearing on reset
 */
class ConversationStateService
{
    private const CACHE_PREFIX = 'chatbot_state:';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get conversation state, initializing with defaults if needed.
     *
     * @return array<string, mixed>
     */
    public function getState(string $sessionId): array
    {
        return Cache::get(self::CACHE_PREFIX.$sessionId, $this->defaultState());
    }

    /**
     * Save conversation state.
     *
     * @param  array<string, mixed>  $state
     */
    public function saveState(string $sessionId, array $state): void
    {
        $state['updated_at'] = now()->toIso8601String();
        Cache::put(self::CACHE_PREFIX.$sessionId, $state, now()->addSeconds(self::CACHE_TTL));
    }

    /**
     * Get or create state entry for a session.
     *
     * @return array<string, mixed>
     */
    public function getOrCreate(string $sessionId): array
    {
        if (! Cache::has(self::CACHE_PREFIX.$sessionId)) {
            $this->saveState($sessionId, $this->defaultState());
        }

        return $this->getState($sessionId);
    }

    /**
     * Update state with resolved fields.
     *
     * @param  array<string, mixed>  $updates
     */
    public function update(string $sessionId, array $updates): void
    {
        $state = $this->getState($sessionId);

        foreach ($updates as $key => $value) {
            if ($value !== null) {
                $state[$key] = $value;
            }
        }

        // Clear pending_field if the field it was waiting for is now filled
        if (isset($state['pending_field']) && isset($updates[$state['pending_field'].'_id'])) {
            $state['pending_field'] = null;
            $state['last_question'] = null;
        }

        $this->saveState($sessionId, $state);
    }

    /**
     * Set a field as pending (must be answered in next message).
     */
    public function setPendingField(string $sessionId, string $field, string $question): void
    {
        $state = $this->getState($sessionId);
        $state['pending_field'] = $field;
        $state['last_question'] = $question;
        $this->saveState($sessionId, $state);
    }

    /**
     * Clear pending field.
     */
    public function clearPendingField(string $sessionId): void
    {
        $state = $this->getState($sessionId);
        $state['pending_field'] = null;
        $state['last_question'] = null;
        $this->saveState($sessionId, $state);
    }

    /**
     * Check if a field is currently pending.
     */
    public function isPending(string $sessionId, string $field): bool
    {
        $state = $this->getState($sessionId);

        return $state['pending_field'] === $field;
    }

    /**
     * Get pending field if any.
     */
    public function getPendingField(string $sessionId): ?string
    {
        return $this->getState($sessionId)['pending_field'] ?? null;
    }

    /**
     * Reset entire conversation state.
     */
    public function reset(string $sessionId): void
    {
        Cache::forget(self::CACHE_PREFIX.$sessionId);
    }

    /**
     * Check if state has all required fields for provider search.
     */
    public function isReadyForSearch(string $sessionId): bool
    {
        $state = $this->getState($sessionId);

        // Must have at least one of: city or category
        $hasCity = filled($state['city_id']);
        $hasCategory = filled($state['category_id']);

        return ($hasCity || $hasCategory) && empty($state['pending_field']);
    }

    /**
     * Get missing required fields.
     *
     * @return array<int, string>
     */
    public function getMissingFields(string $sessionId): array
    {
        $state = $this->getState($sessionId);
        $missing = [];

        if (empty($state['city_id'])) {
            $missing[] = 'city';
        }
        if (empty($state['category_id']) && empty($state['service_query'])) {
            $missing[] = 'service';
        }

        return $missing;
    }

    /**
     * Default state structure.
     *
     * @return array<string, mixed>
     */
    private function defaultState(): array
    {
        return [
            'intent' => null,
            'city_id' => null,
            'city_name' => null,
            'category_id' => null,
            'subcategory_id' => null,
            'service_query' => null,
            'min_experience_years' => null,
            'pending_field' => null,
            'last_question' => null,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
