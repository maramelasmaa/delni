<?php

namespace App\Services\Chatbot;

use App\Models\City;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Conversational Chatbot V3 - Stateful orchestrator.
 *
 * NEW APPROACH: Hybrid intelligent conversation grounded in database
 *
 * Flow:
 * 1. Load conversation state from cache
 * 2. Check if greeting (cheap, deterministic - no AI)
 * 3. Call DeepSeekConversationService (one AI call)
 *    - Understands intent
 *    - Extracts: service, city, experience_years
 *    - Asks follow-up if needed
 * 4. Search database with extracted intent
 * 5. Update conversation state
 * 6. Return response with DB results
 *
 * Never invents: Services, providers, or results
 * Token budget: ~600-700 per message
 * Cost: ~$0.0002-0.0003 per message
 */
class ChatOrchestratorService
{
    public function __construct(
        private IntentDetectionService $intentDetection,
        private DeepSeekConversationService $conversation,
        private ProviderSearchForChatService $providerSearch,
    ) {}

    /**
     * Process user message with conversational AI.
     *
     * @param  array<string, mixed>  $conversationState Compact state from cache
     * @return array<string, mixed>
     */
    public function handle(
        string $message,
        array $conversationState = [],
        ?string $conversationId = null,
    ): array {
        $conversationId = $conversationId ?? $this->generateId();

        try {
            // STAGE 1: Cheap deterministic checks (no AI call)

            // Check if greeting
            $detectedIntent = $this->intentDetection->detect($message);
            if ($detectedIntent === 'greeting') {
                $this->updateState($conversationId, ['last_intent' => 'greeting']);

                return [
                    'success' => true,
                    'message' => 'وعليكم السلام! 👋 كيف يمكنني مساعدتك؟ ابحث عن خدمة معينة أو أخبرني بالمدينة.',
                    'intent' => 'greeting',
                    'providers' => [],
                    'conversation_id' => $conversationId,
                ];
            }

            // STAGE 2: Use DeepSeek for intelligent conversation + extraction

            // Get max 5 recent providers from state to send as context
            $recentProvidersIds = $conversationState['last_results_ids'] ?? [];
            $contextProviders = $this->getProviderContext($recentProvidersIds);

            // Call DeepSeek (single call per message)
            $aiResponse = $this->conversation->chat(
                userMessage: $message,
                conversationState: $conversationState,
                dbProviders: $contextProviders,
            );

            if (!$aiResponse['success']) {
                // Fallback if DeepSeek fails
                return [
                    'success' => true,
                    'message' => $aiResponse['message'],
                    'intent' => 'clarify',
                    'providers' => [],
                    'conversation_id' => $conversationId,
                ];
            }

            // Extract intent from AI response
            $extractedService = $aiResponse['extracted_service'];
            $extractedCity = $aiResponse['extracted_city'];
            $extractedExperience = $aiResponse['extracted_experience'];

            // Update state with extracted info
            if ($extractedService) {
                $conversationState['service_query'] = $extractedService;
            }
            if ($extractedCity) {
                $conversationState['city'] = $extractedCity;
            }
            if ($extractedExperience) {
                $conversationState['min_experience_years'] = $extractedExperience;
            }

            // STAGE 3: If we have a service, search the database

            if ($extractedService) {
                $cityId = null;
                if ($extractedCity) {
                    $cityId = $this->resolveCityId($extractedCity);
                }

                // Search database
                $providers = $this->providerSearch->searchSemantic(
                    providerNameQuery: null,
                    businessNameQuery: null,
                    serviceQuery: $extractedService,
                    cityId: $cityId,
                    categoryHint: null,
                );

                // Store recent provider IDs for context
                $conversationState['last_results_ids'] = $providers->pluck('id')->take(5)->toArray();
                $this->updateState($conversationId, $conversationState);

                // Return results (even if empty - be honest about DB)
                return [
                    'success' => true,
                    'message' => $aiResponse['message'], // Use AI-generated message
                    'intent' => $providers->isEmpty() ? 'no_results' : 'search',
                    'providers' => $providers->toArray(),
                    'conversation_id' => $conversationId,
                ];
            }

            // No service extracted yet - just respond conversationally
            $this->updateState($conversationId, $conversationState);

            return [
                'success' => true,
                'message' => $aiResponse['message'],
                'intent' => $aiResponse['intent'] ?? 'clarify',
                'providers' => [],
                'conversation_id' => $conversationId,
            ];
        } catch (\Throwable $e) {
            Log::error('Chatbot orchestrator error', [
                'error' => $e->getMessage(),
                'message' => $message,
            ]);

            return [
                'success' => false,
                'message' => 'حدث خطأ. حاول مرة أخرى.',
                'intent' => 'error',
                'providers' => [],
                'conversation_id' => $conversationId,
            ];
        }
    }

    /**
     * Resolve city name to ID.
     */
    private function resolveCityId(?string $cityName): ?int
    {
        if (!$cityName) {
            return null;
        }

        return City::where('is_active', true)
            ->where(function ($q) use ($cityName) {
                $q->where('name_ar', 'like', "%{$cityName}%")
                    ->orWhere('name', 'like', "%{$cityName}%");
            })
            ->value('id');
    }

    /**
     * Get provider context for DeepSeek (max 5, minimal fields).
     *
     * @param  array<int>  $providerIds
     * @return array<int, array<string, mixed>>
     */
    private function getProviderContext(array $providerIds): array
    {
        if (empty($providerIds)) {
            return [];
        }

        $providers = $this->providerSearch->searchSemantic()
            ->whereIn('id', array_slice($providerIds, 0, 5))
            ->get()
            ->map(function ($p) {
                return [
                    'name' => $p['name'] ?? 'Unknown',
                    'city' => $p['city'] ?? 'Unknown',
                    'category' => $p['category'] ?? 'General',
                    'rating' => $p['rating'] ?? 0,
                ];
            })
            ->toArray();

        return $providers;
    }

    /**
     * Update conversation state in cache.
     */
    private function updateState(string $conversationId, array $state): void
    {
        Cache::put(
            "chatbot:state:{$conversationId}",
            $state,
            now()->addHours(24),
        );
    }

    /**
     * Generate secure conversation ID.
     */
    private function generateId(): string
    {
        return 'chat_'.bin2hex(random_bytes(16));
    }
}
