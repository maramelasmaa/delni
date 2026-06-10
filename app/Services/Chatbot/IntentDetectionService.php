<?php

namespace App\Services\Chatbot;

/**
 * Detects user intent from message text using deterministic rules.
 *
 * Supported intents:
 * - greeting: مرحبا، أهلا، السلام عليكم، شلونك
 * - provider_search: looking for specific services
 * - provider_join_question: how to register as provider
 * - support_question: how to use app, feature questions
 * - city_missing: search detected but no city specified
 * - category_missing: unclear which category
 * - out_of_scope: not related to Delni
 */
class IntentDetectionService
{
    private array $greetingPatterns = [
        'أهلا',
        'مرحبا',
        'السلام عليكم',
        'السلام',
        'شلونك',
        'كيفك',
        'كيفك',
        'شنو أخبارك',
        'تمام التمام',
        'السلام والرحمة',
        'صباح الخير',
        'مساء الخير',
    ];

    private array $joinQuestionPatterns = [
        'كيف نسجل',
        'كيفية التسجيل',
        'كيف أصير',
        'كيف أسجل',
        'نسجيل كمقدم',
        'تسجيل خدمة',
        'الانضمام',
        'كيف أنضم',
        'كيف أصير مزود خدمات',
    ];

    private array $supportQuestionPatterns = [
        'شنو دلني',
        'ما هي دلني',
        'شن دلني',
        'ما هي الخدمة',
        'كيف أستخدم',
        'كيف يعمل',
        'هاي البرنامج',
        'شرح',
        'ساعدني',
        'مساعدة',
    ];

    /**
     * Detect intent from user message.
     *
     * Returns array with:
     * - intent: string (greeting, provider_search, etc.)
     * - confidence: 'high', 'medium', 'low'
     * - details: additional metadata
     *
     * @return array<string, mixed>
     */
    public function detect(string $message): array
    {
        $message = mb_strtolower(trim($message), 'UTF-8');

        if (empty($message)) {
            return [
                'intent' => 'greeting',
                'confidence' => 'high',
                'details' => [],
            ];
        }

        // Check greeting first
        if ($this->isGreeting($message)) {
            return [
                'intent' => 'greeting',
                'confidence' => 'high',
                'details' => [],
            ];
        }

        // Check join question
        if ($this->isJoinQuestion($message)) {
            return [
                'intent' => 'provider_join_question',
                'confidence' => 'high',
                'details' => [],
            ];
        }

        // Check support question
        if ($this->isSupportQuestion($message)) {
            return [
                'intent' => 'support_question',
                'confidence' => 'high',
                'details' => [],
            ];
        }

        // Default to provider_search (most common intent)
        return [
            'intent' => 'provider_search',
            'confidence' => 'high',
            'details' => [],
        ];
    }

    /**
     * Check if message is a greeting.
     */
    private function isGreeting(string $message): bool
    {
        foreach ($this->greetingPatterns as $pattern) {
            if (str_contains($message, mb_strtolower($pattern, 'UTF-8'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if message is asking about joining as provider.
     */
    private function isJoinQuestion(string $message): bool
    {
        foreach ($this->joinQuestionPatterns as $pattern) {
            if (str_contains($message, mb_strtolower($pattern, 'UTF-8'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if message is asking about the app itself.
     */
    private function isSupportQuestion(string $message): bool
    {
        foreach ($this->supportQuestionPatterns as $pattern) {
            if (str_contains($message, mb_strtolower($pattern, 'UTF-8'))) {
                return true;
            }
        }

        return false;
    }
}
