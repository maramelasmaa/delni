<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\SendMessageRequest;
use App\Models\Category;
use App\Models\City;
use App\Services\Chatbot\IntentDetectionService;
use App\Services\Chatbot\ProviderSearchForChatService;
use App\Services\Chatbot\SafeIntentExtractor;
use Illuminate\Http\JsonResponse;

/**
 * Chatbot API V2 - Intent-driven search.
 *
 * Flow:
 * 1. Receive message
 * 2. Extract intent (specialty, city, preferences)
 * 3. If confident: search providers
 * 4. If unclear: ask clarification
 * 5. Return results (never invented)
 */
class ChatControllerV2 extends Controller
{
    public function __construct(
        private IntentDetectionService $intentDetection,
        private SafeIntentExtractor $extractor,
        private ProviderSearchForChatService $searchService,
    ) {}

    /**
     * Send message to chatbot.
     *
     * Returns either:
     * - Clarification question (if extraction unclear)
     * - Provider search results (if confident)
     */
    public function message(SendMessageRequest $request): JsonResponse
    {
        $message = $request->validated('message');
        $conversationId = $request->validated('conversation_id') ?? $this->generateId();

        // Check if it's a greeting first
        $detectedIntent = $this->intentDetection->detect($message);
        if ($detectedIntent === 'greeting') {
            return response()->json([
                'type' => 'greeting',
                'message' => 'وعليكم السلام! 👋 كيف يمكنني مساعدتك؟ ابحث عن خدمة معينة أو أخبرني بالمدينة.',
                'conversation_id' => $conversationId,
            ]);
        }

        // Extract intent from message for service search
        $intent = $this->extractor->extract($message);

        // If extraction unclear, ask for clarification
        if ($intent->needsClarification) {
            return response()->json([
                'type' => 'clarification',
                'question' => $intent->clarificationQuestion,
                'conversation_id' => $conversationId,
            ]);
        }

        // If not confident enough, ask for clarification
        if (! $intent->isConfident()) {
            return response()->json([
                'type' => 'clarification',
                'question' => 'ممكن تعطيني معلومات اكثر عن نوع الخدمة اللي تبحث عنها؟',
                'conversation_id' => $conversationId,
            ]);
        }

        // Search providers based on extracted intent
        $providers = $this->searchService->searchSemantic(
            providerNameQuery: null,
            businessNameQuery: null,
            serviceQuery: $intent->specialty,
            cityId: $this->resolveCity($intent->city),
            categoryHint: null,
        );

        // No results
        if ($providers->isEmpty()) {
            return response()->json([
                'type' => 'no_results',
                'message' => 'ما لقيناش نتائج مطابقة حالياً. جرّب مدينة أخرى أو خدمة مختلفة.',
                'conversation_id' => $conversationId,
            ]);
        }

        // Return results
        return response()->json([
            'type' => 'results',
            'count' => $providers->count(),
            'message' => "لقيتلك {$providers->count()} مقدمي خدمة:",
            'providers' => $providers->toArray(),
            'conversation_id' => $conversationId,
        ]);
    }

    /**
     * Reset conversation.
     */
    public function reset(): JsonResponse
    {
        return response()->json([
            'message' => 'Conversation reset',
            'conversation_id' => $this->generateId(),
        ]);
    }

    /**
     * Initialize chatbot UI (categories, featured providers).
     */
    public function init(): JsonResponse
    {
        // Get categories from database
        $categories = Category::where('is_active', true)
            ->get(['id', 'name_ar', 'name', 'icon_id'])
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name_ar' => $cat->name_ar,
                'name' => $cat->name,
            ])
            ->toArray();

        // Get featured providers
        $featured = $this->searchService->getFeaturedProviders(6);

        return response()->json([
            'categories' => $categories,
            'featured_providers' => $featured->toArray(),
            'conversation_id' => $this->generateId(),
        ]);
    }

    /**
     * Resolve city name to ID.
     */
    private function resolveCity(?string $cityName): ?int
    {
        if (! $cityName) {
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
     * Generate unique conversation ID.
     */
    private function generateId(): string
    {
        return 'chat_'.bin2hex(random_bytes(16));
    }
}
