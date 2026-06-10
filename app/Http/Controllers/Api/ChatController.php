<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\SendMessageRequest;
use App\Services\Chatbot\CategoryResolverService;
use App\Services\Chatbot\ChatOrchestratorService;
use App\Services\Chatbot\ProviderSearchForChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private ChatOrchestratorService $orchestrator,
        private ProviderSearchForChatService $searchService,
        private CategoryResolverService $categoryResolver,
    ) {}

    /**
     * Send a message to the chatbot.
     */
    public function message(SendMessageRequest $request): JsonResponse
    {
        $message = $request->validated('message');
        $conversationId = $request->validated('conversation_id');

        $response = $this->orchestrator->handle(
            message: $message,
            user: auth()->user(),
            metadata: [
                'conversation_id' => $conversationId,
            ],
        );

        return response()->json($response);
    }

    /**
     * Reset a conversation.
     */
    public function reset(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Conversation reset',
            'new_conversation_id' => $this->generateConversationId(),
        ]);
    }

    /**
     * Get initial chat UI data (categories, featured providers).
     */
    public function init(): JsonResponse
    {
        $categories = $this->categoryResolver->getAllCategories()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->localized_name,
                'icon' => $cat->icon?->file_path,
            ])
            ->toArray();

        $featured = $this->searchService->getFeaturedProviders(6)
            ->map(fn ($p) => $p->toArray())
            ->toArray();

        return response()->json([
            'categories' => $categories,
            'featured_providers' => $featured,
            'conversation_id' => $this->generateConversationId(),
        ]);
    }

    /**
     * Generate a unique conversation ID.
     */
    private function generateConversationId(): string
    {
        return 'chat_'.uniqid('', true);
    }
}
