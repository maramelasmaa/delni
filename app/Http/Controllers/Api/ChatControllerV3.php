<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\SendMessageRequest;
use App\Services\Chatbot\ChatOrchestratorService;
use App\Services\Chatbot\ConversationStateRepository;
use Illuminate\Http\JsonResponse;

/**
 * Chatbot V3 API - Conversational stateful AI.
 *
 * Endpoints (rate-limited via middleware):
 * - POST /api/chat/v3/message - Send message, get response
 * - GET /api/chat/v3/init - Initialize conversation
 * - POST /api/chat/v3/reset - Reset conversation
 *
 * Architecture:
 * - Rate limiting: ChatbotRateLimit middleware (30/hour guests, 60/day auth)
 * - State: ConversationStateRepository (24h cache)
 * - Logic: ChatOrchestratorService (one DeepSeek call per message)
 * - Validation: SendMessageRequest (auto-generates conversation_id)
 *
 * Per §10: Methods <10 lines, lean logic.
 */
class ChatControllerV3 extends Controller
{
    public function __construct(
        private ChatOrchestratorService $orchestrator,
        private ConversationStateRepository $stateRepo,
    ) {}

    /**
     * Send message to chatbot.
     *
     * Middleware handles rate limiting.
     */
    public function message(SendMessageRequest $request): JsonResponse
    {
        $conversationId = $request->validated('conversation_id');
        $state = $this->stateRepo->load($conversationId);

        $response = $this->orchestrator->handle(
            message: $request->validated('message'),
            conversationState: $state,
            conversationId: $conversationId,
        );

        $this->stateRepo->save($conversationId, $state);

        return response()->json($response);
    }

    /**
     * Initialize new conversation.
     */
    public function init(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'conversation_id' => 'chat_'.bin2hex(random_bytes(16)),
            'message' => 'أهلاً بك! 👋 كيف يمكنني مساعدتك؟',
        ]);
    }

    /**
     * Reset conversation and start new.
     */
    public function reset(SendMessageRequest $request): JsonResponse
    {
        $this->stateRepo->clear($request->validated('conversation_id'));

        return $this->init();
    }
}
