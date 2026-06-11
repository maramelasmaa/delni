<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\SendMessageRequest;
use App\Services\Chatbot\ChatOrchestratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Chatbot V3 API - Conversational AI with stateful memory.
 *
 * Endpoints:
 * - POST /api/chat/v3/message - Send message and get response
 * - GET /api/chat/v3/init - Start new conversation
 * - POST /api/chat/v3/reset - Reset conversation
 *
 * Features:
 * - Stateful conversation (remembers service, city, experience)
 * - Rate-limited (10/hour guests, 50/day authenticated)
 * - DB-grounded (only returns actual providers)
 * - One DeepSeek call per message
 */
class ChatControllerV3 extends Controller
{
    private const RATE_LIMIT_GUESTS = 10; // per hour
    private const RATE_LIMIT_USERS = 50; // per day
    private const STATE_CACHE_TTL = 86400; // 24 hours

    public function __construct(
        private ChatOrchestratorService $orchestrator,
    ) {}

    /**
     * Send message to chatbot.
     */
    public function message(SendMessageRequest $request): JsonResponse
    {
        $message = $request->validated('message');
        $conversationId = $request->validated('conversation_id');

        // Rate limiting
        if (!$this->checkRateLimit($conversationId)) {
            return response()->json([
                'success' => false,
                'message' => 'وصلت للحد المسموح من الرسائل. حاول بعد شوية.',
                'retry_after' => 300,
            ], 429);
        }

        // Load conversation state
        $state = $this->loadState($conversationId);

        // Process message
        $response = $this->orchestrator->handle(
            message: $message,
            conversationState: $state,
            conversationId: $conversationId,
        );

        // Save updated state
        $this->saveState($conversationId, $state);

        return response()->json($response);
    }

    /**
     * Initialize chatbot session.
     */
    public function init(): JsonResponse
    {
        $conversationId = 'chat_'.bin2hex(random_bytes(16));

        return response()->json([
            'success' => true,
            'conversation_id' => $conversationId,
            'message' => 'أهلاً بك! 👋 كيف يمكنني مساعدتك؟',
        ]);
    }

    /**
     * Reset conversation.
     */
    public function reset(): JsonResponse
    {
        $conversationId = 'chat_'.bin2hex(random_bytes(16));
        $this->clearState($conversationId);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversationId,
            'message' => 'تم إعادة تعيين المحادثة.',
        ]);
    }

    /**
     * Check rate limits.
     */
    private function checkRateLimit(string $conversationId): bool
    {
        $key = auth()->check()
            ? "chat:user:".auth()->id()
            : "chat:guest:".$conversationId;

        $limit = auth()->check() ? self::RATE_LIMIT_USERS : self::RATE_LIMIT_GUESTS;
        $window = auth()->check() ? 1440 : 60;

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return false;
        }

        RateLimiter::hit($key, $window * 60);

        return true;
    }

    /**
     * Load conversation state from cache.
     */
    private function loadState(string $conversationId): array
    {
        return Cache::get("chatbot:state:{$conversationId}", [
            'service_query' => null,
            'city' => null,
            'min_experience_years' => null,
            'last_results_ids' => [],
            'last_intent' => null,
        ]);
    }

    /**
     * Save conversation state to cache.
     */
    private function saveState(string $conversationId, array $state): void
    {
        Cache::put(
            "chatbot:state:{$conversationId}",
            $state,
            now()->addSeconds(self::STATE_CACHE_TTL),
        );
    }

    /**
     * Clear conversation state.
     */
    private function clearState(string $conversationId): void
    {
        Cache::forget("chatbot:state:{$conversationId}");
    }
}
