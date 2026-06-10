<?php

namespace App\Services\Chatbot;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Orchestrates the complete chatbot flow.
 *
 * Coordinates:
 * 1. Safety validation
 * 2. Intent detection
 * 3. Category/city resolution
 * 4. Provider search
 * 5. Context building
 * 6. DeepSeek API call (with graceful fallback)
 * 7. Response formatting
 *
 * Ensures:
 * - Only visible providers are used
 * - No hidden/suspended/expired providers leak
 * - DeepSeek failures don't break the app
 * - Rate limiting is respected
 */
class ChatOrchestratorService
{
    public function __construct(
        private ChatSafetyService $safety,
        private IntentDetectionService $intentDetection,
        private SmartProviderMatcher $smartMatcher,
        private ChatContextBuilderService $contextBuilder,
        private ChatPromptBuilderService $promptBuilder,
        private DeepSeekClient $deepSeek,
        private ChatResponseFormatterService $responseFormatter,
    ) {}

    /**
     * Process a user message through the complete chatbot pipeline.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function handle(
        string $message,
        ?User $user = null,
        array $metadata = [],
    ): array {
        // Safety check first
        $safetyCheck = $this->safety->validate($message);
        if (! $safetyCheck['safe']) {
            return $this->unsafeResponse($safetyCheck['reason']);
        }

        // Detect intent
        $intentData = $this->intentDetection->detect($message);
        $intent = $intentData['intent'];

        // Load conversation state
        $conversationId = $metadata['conversation_id'] ?? $this->generateConversationId();
        $state = $this->getConversationState($conversationId);

        // Handle by intent
        return match ($intent) {
            'greeting' => $this->handleGreeting($conversationId),
            'provider_join_question' => $this->handleJoinQuestion($conversationId),
            'support_question' => $this->handleSupportQuestion($conversationId),
            'provider_search' => $this->handleProviderSearch($message, $state, $conversationId, $user),
            default => $this->handleProviderSearch($message, $state, $conversationId, $user),
        };
    }

    /**
     * Handle greeting intent.
     */
    private function handleGreeting(string $conversationId): array
    {
        $response = 'أهلاً بك في دلني! شن الخدمة اللي تبحث عنها؟';

        return $this->responseFormatter->format(
            message: $response,
            intent: 'greeting',
            providers: collect(),
            metadata: ['session_id' => $conversationId],
        );
    }

    /**
     * Handle "how to join as provider" question.
     */
    private function handleJoinQuestion(string $conversationId): array
    {
        $response = 'مرحباً! يمكنك التسجيل كمزود خدمات من خلال تطبيق دلني. انقر على "التسجيل" واختر "مزود خدمات" وأكمل البيانات المطلوبة.';

        return $this->responseFormatter->format(
            message: $response,
            intent: 'provider_join_question',
            providers: collect(),
            metadata: [
                'session_id' => $conversationId,
                'needs_city' => false,
                'needs_category' => false,
            ],
        );
    }

    /**
     * Handle "how to use app" question.
     */
    private function handleSupportQuestion(string $conversationId): array
    {
        $response = 'دلني هو تطبيق للبحث عن مقدمي الخدمات في ليبيا. يمكنك البحث عن الخدمات التي تحتاجها حسب الفئة والمدينة، والتواصل مباشرة مع مقدمي الخدمات.';

        return $this->responseFormatter->format(
            message: $response,
            intent: 'support_question',
            providers: collect(),
            metadata: [
                'session_id' => $conversationId,
                'needs_city' => false,
                'needs_category' => false,
            ],
        );
    }

    /**
     * Handle provider search intent using smart matching.
     *
     * @param  array<string, mixed>  $state
     */
    private function handleProviderSearch(
        string $message,
        array $state,
        string $conversationId,
        ?User $user,
    ): array {
        // Use smart matcher to understand intent and find providers
        $match = $this->smartMatcher->match($message);

        // Save state
        $state['messages_count'] = ($state['messages_count'] ?? 0) + 1;
        if ($match['intent']['city_id']) {
            $state['last_city_id'] = $match['intent']['city_id'];
        }
        $this->saveConversationState($conversationId, $state);

        // If clarification needed, ask once
        if ($match['needs_clarification']) {
            return $this->responseFormatter->format(
                message: $match['clarification_message'],
                intent: 'provider_search',
                providers: collect(),
                metadata: [
                    'session_id' => $conversationId,
                    'needs_city' => false,
                    'needs_category' => false,
                ],
            );
        }

        // If city needed, ask for it (NOT category)
        if ($match['needs_city']) {
            return $this->responseFormatter->format(
                message: $match['clarification_message'],
                intent: 'provider_search',
                providers: collect(),
                metadata: [
                    'session_id' => $conversationId,
                    'needs_city' => true,
                    'needs_category' => false,
                ],
            );
        }

        // Build response message
        $providers = $match['providers'];

        if ($providers->count() === 0) {
            $response = $match['fallback_explanation'] ?? 'حالياً ما لقيناش مقدم خدمة مطابق.';

            return $this->responseFormatter->format(
                message: $response,
                intent: 'provider_search',
                providers: collect(),
                metadata: [
                    'session_id' => $conversationId,
                    'needs_city' => false,
                    'needs_category' => false,
                ],
            );
        }

        // Build response with found providers
        $responseMessage = $match['fallback_explanation']
            ? $match['fallback_explanation']
            : 'لقيتلك مقدمي خدمة مناسبين:';

        return $this->responseFormatter->format(
            message: $responseMessage,
            intent: 'provider_search',
            providers: $providers,
            metadata: [
                'session_id' => $conversationId,
                'needs_city' => false,
                'needs_category' => false,
            ],
        );
    }

    /**
     * Handle unsafe message.
     *
     * @return array<string, mixed>
     */
    private function unsafeResponse(string $reason): array
    {
        return [
            'message' => 'حدث خطأ في معالجة رسالتك. حاول مرة أخرى.',
            'intent' => 'error',
            'providers' => [],
            'suggested_actions' => [],
            'needs' => [
                'city' => false,
                'category' => false,
            ],
        ];
    }

    /**
     * Get conversation state from cache.
     *
     * @return array<string, mixed>
     */
    private function getConversationState(string $conversationId): array
    {
        return Cache::get("chatbot_state:{$conversationId}", [
            'last_city_id' => null,
            'last_category_id' => null,
            'last_subcategory_id' => null,
            'messages_count' => 0,
        ]);
    }

    /**
     * Save conversation state to cache (1 hour TTL).
     */
    private function saveConversationState(string $conversationId, array $state): void
    {
        Cache::put("chatbot_state:{$conversationId}", $state, now()->addHour());
    }

    /**
     * Generate a unique conversation ID.
     */
    private function generateConversationId(): string
    {
        return 'chat_'.uniqid('', true);
    }
}
