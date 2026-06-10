<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Cache;

/**
 * Manages conversation state across messages.
 *
 * Tracks:
 * - Current intent (provider_search, greeting, etc.)
 * - Resolved fields (category, subcategory, city)
 * - Pending required fields (city, experience, etc.)
 * - Last question asked
 *
 * Enables multi-turn conversations without restarting from zero.
 */
class ConversationStateManager
{
    private const CACHE_PREFIX = 'chatbot_conversation:';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get conversation state for a session.
     */
    public function getState(string $sessionId): array
    {
        return Cache::get(self::CACHE_PREFIX . $sessionId, [
            'intent' => null,
            'category_id' => null,
            'category_slug' => null,
            'subcategory_id' => null,
            'city_id' => null,
            'city_name' => null,
            'pending_field' => null,
            'last_question' => null,
            'experience_years_min' => null,
            'remote_preferred' => false,
            'message_count' => 0,
        ]);
    }

    /**
     * Save conversation state.
     */
    public function saveState(string $sessionId, array $state): void
    {
        Cache::put(self::CACHE_PREFIX . $sessionId, $state, now()->addSeconds(self::CACHE_TTL));
    }

    /**
     * Set pending field that needs to be resolved in next message.
     */
    public function setPendingField(string $sessionId, string $field, string $lastQuestion): void
    {
        $state = $this->getState($sessionId);
        $state['pending_field'] = $field;
        $state['last_question'] = $lastQuestion;
        $this->saveState($sessionId, $state);
    }

    /**
     * Clear pending field once resolved.
     */
    public function clearPendingField(string $sessionId): void
    {
        $state = $this->getState($sessionId);
        $state['pending_field'] = null;
        $state['last_question'] = null;
        $this->saveState($sessionId, $state);
    }

    /**
     * Reset entire conversation state.
     */
    public function reset(string $sessionId): void
    {
        Cache::forget(self::CACHE_PREFIX . $sessionId);
    }

    /**
     * Update state with detected intent fields.
     */
    public function updateWithIntent(string $sessionId, array $intent): void
    {
        $state = $this->getState($sessionId);

        if ($intent['category_slug'] ?? null) {
            $state['category_slug'] = $intent['category_slug'];
        }

        if ($intent['city_id'] ?? null) {
            $state['city_id'] = $intent['city_id'];
            $state['city_name'] = $intent['city'] ?? null;
        }

        if ($intent['experience_years_min'] ?? null) {
            $state['experience_years_min'] = $intent['experience_years_min'];
        }

        if ($intent['remote_preferred'] ?? false) {
            $state['remote_preferred'] = true;
        }

        $state['message_count'] = ($state['message_count'] ?? 0) + 1;
        $this->saveState($sessionId, $state);
    }
}
