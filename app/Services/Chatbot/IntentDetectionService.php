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
        // Arabic greetings - Formal Islamic
        'السلام عليكم',
        'السلام عليكم ورحمة الله',
        'السلام عليكم ورحمة الله وبركاته',
        'وعليكم السلام',
        'وعليكم السلام ورحمة الله',
        'وعليكم السلام ورحمة الله وبركاته',
        'السلام',
        // Arabic greetings - Casual
        'أهلا',
        'أهلا وسهلا',
        'أهلا بك',
        'مرحبا',
        'مرحبا بك',
        'هلا',
        'هلا وسهلا',
        // Arabic greetings - "How are you"
        'شلونك',
        'شلون أحوالك',
        'شنو أخبارك',
        'كيفك',
        'كيف حالك',
        'كيفك أنت',
        'كيفك أنتي',
        'كيف أحوالك',
        'شن أخبارك',
        'شنو حالك',
        // Arabic greetings - Responses
        'تمام',
        'تمام التمام',
        'بخير',
        'بخير الحمد لله',
        'الحمد لله',
        'ماشي الحال',
        'تمام تمام',
        'والله تمام',
        'كل التمام',
        'أنا بخير',
        'الحمد لله على السلامة',
        // Arabic greetings - Time-specific
        'صباح الخير',
        'صباح النور',
        'مساء الخير',
        'مساء النور',
        'ليلة طيبة',
        'تصبح على خير',
        // Arabic greetings - Other
        'السلام والرحمة',
        'السلام عليكن',
        'سلام عليكم',
        // English greetings - Standard
        'hello',
        'hi',
        'hey',
        'greetings',
        'good morning',
        'good afternoon',
        'good evening',
        'how are you',
        'what\'s up',
        // English greetings - Casual
        'yo',
        'sup',
        'hey bro',
        'bro',
        'wassup',
        'what\'s good',
        'how\'s it going',
        'how you doing',
    ];

    private array $joinQuestionPatterns = [
        // Arabic
        'كيف نسجل',
        'كيفية التسجيل',
        'كيف أصير',
        'كيف أسجل',
        'نسجيل كمقدم',
        'تسجيل خدمة',
        'الانضمام',
        'كيف أنضم',
        'كيف أصير مزود خدمات',
        // English
        'how to register',
        'how do i sign up',
        'how to join',
        'become a provider',
        'become a service provider',
        'register as provider',
        'sign up as provider',
    ];

    private array $supportQuestionPatterns = [
        // Arabic
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
        // English
        'what is delni',
        'how to use',
        'how does it work',
        'how to find',
        'how to contact',
        'help',
        'support',
        'explain',
        'tell me about',
        'about delni',
        'features',
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
        $lowerMessage = mb_strtolower($message, 'UTF-8');

        foreach ($this->greetingPatterns as $pattern) {
            $lowerPattern = mb_strtolower($pattern, 'UTF-8');
            if (str_contains($lowerMessage, $lowerPattern)) {
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
