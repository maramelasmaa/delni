<?php

namespace App\Services\Chatbot;

use App\Models\Category;
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
        // CRITICAL: Check for pending field first (multi-turn conversation)
        $pendingField = $state['pending_field'] ?? null;

        if ($pendingField === 'city') {
            // User is answering "which city?" question
            $cityResolver = app(CityAliasResolver::class);
            $resolvedCity = $cityResolver->resolve($message);

            if ($resolvedCity) {
                // City successfully resolved!
                $state['city_id'] = $resolvedCity['id'];
                $state['pending_field'] = null;
                $this->saveConversationState($conversationId, $state);

                // Continue with provider search using resolved city
                return $this->performProviderSearch($state, $conversationId);
            }

            // City not understood
            return $this->responseFormatter->format(
                message: 'ما فهمت اسم المدينة. جرّب: طرابلس، بنغازي، مصراتة، طبرق',
                intent: 'provider_search',
                providers: collect(),
                metadata: [
                    'session_id' => $conversationId,
                    'needs_city' => true,
                    'needs_category' => false,
                ],
            );
        }

        // No pending field - fresh intent detection
        $match = $this->smartMatcher->match($message);

        // Save detected intent to state
        if ($match['intent']['category_slug'] ?? null) {
            $state['category_slug'] = $match['intent']['category_slug'];
        }
        if ($match['intent']['city_id'] ?? null) {
            $state['city_id'] = $match['intent']['city_id'];
        }
        $state['message_count'] = ($state['message_count'] ?? 0) + 1;
        $this->saveConversationState($conversationId, $state);

        // Clarification needed (service unclear)
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

        // City needed - set as pending and ask
        if ($match['needs_city']) {
            $state['pending_field'] = 'city';
            $this->saveConversationState($conversationId, $state);

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

        // All required fields present - search providers
        return $this->performProviderSearch($state, $conversationId);
    }

    /**
     * Perform actual provider search with complete intent info.
     */
    private function performProviderSearch(array $state, string $conversationId): array
    {
        // Extract category from state
        $categoryId = null;
        if ($state['category_slug'] ?? null) {
            $categoryId = Category::where('slug', $state['category_slug'])->value('id');
        }

        $providers = app(ProviderSearchForChatService::class)->search(
            categoryId: $categoryId,
            cityId: $state['city_id'] ?? null,
        );

        if ($providers->count() === 0) {
            $response = 'حالياً ما لقيناش مقدمي خدمات مطابقين. جرّب مدينة أخرى أو خدمة مختلفة.';

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

        $response = 'لقيتلك مقدمي خدمة مناسبين:';

        return $this->responseFormatter->format(
            message: $response,
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
