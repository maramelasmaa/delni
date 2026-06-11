<?php

namespace App\Services\Chatbot;

use App\Models\Category;
use App\Models\City;
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
        private IntentExtractionService $intentExtraction,
        private ProviderSearchForChatService $providerSearch,
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
     * Handle provider search intent with semantic entity detection.
     *
     * NEW PHILOSOPHY: Search first, ask questions only if necessary.
     *
     * @param  array<string, mixed>  $state
     */
    private function handleProviderSearch(
        string $message,
        array $state,
        string $conversationId,
        ?User $user,
    ): array {
        // Check for pending field (multi-turn conversation)
        $pendingField = $state['pending_field'] ?? null;

        if ($pendingField === 'city') {
            return $this->handleCityResponse($message, $state, $conversationId);
        }

        // Extract intent and entities using semantic understanding
        $extraction = $this->intentExtraction->extract($message);

        // Try semantic search first (aggressive searching)
        if ($extraction['should_search_first']) {
            $providers = $this->providerSearch->searchSemantic(
                providerNameQuery: $extraction['provider_name_query'],
                businessNameQuery: $extraction['business_name_query'],
                serviceQuery: $extraction['service_query'],
                cityId: $this->resolveCityIfNeeded($extraction['city'], $state),
                categoryHint: $this->resolveCategoryIfNeeded($extraction['category_hint']),
            );

            // Found results - return them immediately
            if ($providers->isNotEmpty()) {
                return $this->formatSearchResults(
                    providers: $providers,
                    message: $this->generateSearchResultMessage($extraction, $providers->count()),
                    conversationId: $conversationId,
                );
            }
        }

        // If extraction is very unclear, ask for clarification
        if ($extraction['confidence'] < 0.3) {
            return $this->responseFormatter->format(
                message: 'ما فهمت طلبك بوضوح. شرح ليّ أكثر عن الخدمة اللي تبحث عنها والمدينة إن أمكن.',
                intent: 'provider_search',
                providers: collect(),
                metadata: [
                    'session_id' => $conversationId,
                    'needs_city' => false,
                    'needs_category' => false,
                ],
            );
        }

        // If we have service but missing city, ask for it
        if (filled($extraction['service_query']) && ! $this->resolveCityIfNeeded($extraction['city'], $state)) {
            $state['pending_field'] = 'city';
            $state['service'] = $extraction['service_query'];
            $this->saveConversationState($conversationId, $state);

            return $this->responseFormatter->format(
                message: "في أي مدينة تبحث عن {$extraction['service_query']}؟",
                intent: 'provider_search',
                providers: collect(),
                metadata: [
                    'session_id' => $conversationId,
                    'needs_city' => true,
                    'needs_category' => false,
                ],
            );
        }

        // No results and no clear search criteria
        return $this->responseFormatter->format(
            message: 'ما لقيناش نتيجة مطابقة حالياً. جرّب مدينة أخرى أو خدمة مختلفة.',
            intent: 'provider_search',
            providers: collect(),
            metadata: [
                'session_id' => $conversationId,
                'needs_city' => false,
                'needs_category' => false,
            ],
        );
    }

    /**
     * Handle user's response to "which city?" question.
     *
     * @param  array<string, mixed>  $state
     */
    private function handleCityResponse(
        string $message,
        array $state,
        string $conversationId,
    ): array {
        $cityResolver = app(CityAliasResolver::class);
        $resolvedCity = $cityResolver->resolve($message);

        if (! $resolvedCity) {
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

        // City resolved - search with service + city
        $state['city_id'] = $resolvedCity['id'];
        $state['city_name'] = $resolvedCity['name'];
        $state['pending_field'] = null;
        $this->saveConversationState($conversationId, $state);

        // Search with service hint from previous state
        $providers = $this->providerSearch->searchByService(
            service: $state['service'] ?? 'الخدمة',
            cityId: $state['city_id'],
            categoryHint: null,
        );

        return $this->formatSearchResults(
            providers: $providers,
            message: $providers->isNotEmpty()
                ? "تمام، لقيتلك مقدمي خدمة في {$state['city_name']}:"
                : "للأسف ما لقيناش مقدمي خدمات مطابقين في {$state['city_name']}.",
            conversationId: $conversationId,
        );
    }

    /**
     * Resolve city ID from extraction or state.
     */
    private function resolveCityIfNeeded(?string $cityName, array $state): ?int
    {
        if ($state['city_id'] ?? null) {
            return $state['city_id'];
        }

        if (filled($cityName)) {
            $city = City::where('is_active', true)
                ->where(function ($q) use ($cityName) {
                    $q->where('name_ar', 'like', "%{$cityName}%")
                        ->orWhere('name', 'like', "%{$cityName}%");
                })
                ->first();

            return $city?->id;
        }

        return null;
    }

    /**
     * Resolve category ID from category hint.
     */
    private function resolveCategoryIfNeeded(?string $categoryHint): ?int
    {
        if (! filled($categoryHint)) {
            return null;
        }

        $category = Category::where('is_active', true)
            ->where(function ($q) use ($categoryHint) {
                $q->where('name_ar', 'like', "%{$categoryHint}%")
                    ->orWhere('name', 'like', "%{$categoryHint}%");
            })
            ->first();

        return $category?->id;
    }

    /**
     * Format and return search results.
     */
    private function formatSearchResults(
        mixed $providers,
        string $message,
        string $conversationId,
    ): array {
        return $this->responseFormatter->format(
            message: $message,
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
     * Generate natural response message for search results.
     */
    private function generateSearchResultMessage(array $extraction, int $count): string
    {
        if ($count === 0) {
            return 'ما لقيناش نتيجة مطابقة حالياً. جرّب مدينة أخرى أو خدمة مختلفة.';
        }

        // Build natural message from extraction data
        $parts = [];

        if (filled($extraction['provider_name_query'])) {
            $parts[] = "اسم '{$extraction['provider_name_query']}'";
        }

        if (filled($extraction['service_query'])) {
            $parts[] = "خدمة {$extraction['service_query']}";
        }

        if (filled($extraction['city'])) {
            $parts[] = "في {$extraction['city']}";
        }

        if (empty($parts)) {
            return "لقيتلك {$count} مقدمي خدمة مطابقين:";
        }

        $description = implode(' و ', $parts);

        return "لقيتلك {$count} مقدمي خدمة بـ {$description}:";
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

        // Use DeepSeek to generate a natural response message
        $response = $this->generateResponseWithDeepSeek(
            state: $state,
            providersCount: $providers->count(),
        );

        // Fallback if DeepSeek fails
        if (! $response) {
            $response = 'لقيتلك مقدمي خدمة مناسبين:';
        }

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
     * Generate response message using DeepSeek API.
     *
     * Falls back to deterministic message if API fails.
     */
    private function generateResponseWithDeepSeek(array $state, int $providersCount): ?string
    {
        if (! $this->deepSeek->isEnabled()) {
            return null;
        }

        $city = $state['city_name'] ?? 'المنطقة';
        $category = $state['category_slug'] ?? 'الخدمة';

        $prompt = <<<PROMPT
        أنت مساعد دلني (تطبيق البحث عن مزودي الخدمات الليبي).

        عثرت للتو على $providersCount مزود خدمات في $city يقدمون خدمات $category.

        اكتب رسالة ترحيب قصيرة بالعربية (سطر واحد فقط، بدون قائمة) توضح أنك عثرت على مزودي الخدمات المطابقين.
        اجعلها دافئة وودية ومختصرة.

        الرسالة يجب أن تكون بصيغة عربية طبيعية وتشمل عدد مزودي الخدمات الذين عثرت عليهم والمدينة والخدمة.
        PROMPT;

        $messages = [
            [
                'role' => 'system',
                'content' => 'أنت مساعد ودود يساعد الناس في البحث عن مزودي الخدمات في ليبيا. تتحدث باللهجة الليبية بشكل طبيعي وودي.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ];

        $response = $this->deepSeek->chat($messages);

        return $response ? trim($response) : null;
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
