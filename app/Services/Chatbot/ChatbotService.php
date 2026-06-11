<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Models\User;

class ChatbotService
{
    public function __construct(
        private readonly ChatRateLimiter $rateLimiter,
        private readonly ChatSafetyService $safety,
        private readonly ConversationStateService $state,
        private readonly SearchUnderstandingService $understanding,
        private readonly ProviderSearchForChatService $search,
        private readonly ChatContextBuilder $contextBuilder,
        private readonly ChatResponseBuilder $responseBuilder,
        private readonly DeepSeekConversationService $deepSeek,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function init(string $sessionId): array
    {
        return $this->responseBuilder->welcome($sessionId);
    }

    /**
     * @return array<string, mixed>
     */
    public function reset(string $sessionId): array
    {
        $this->state->forget($sessionId);

        return $this->responseBuilder->welcome($sessionId, 'تم مسح المحادثة. كيف نقدر نساعدك؟');
    }

    /**
     * @return array<string, mixed>
     */
    public function reply(string $message, string $sessionId, ?User $user, string $ipAddress): array
    {
        $limit = $this->rateLimiter->check($user, $ipAddress);
        if (! $limit['allowed']) {
            return $this->responseBuilder->rateLimited($sessionId);
        }

        $safety = $this->safety->validate($message);
        if (! $safety['safe']) {
            return $this->responseBuilder->safetyFallback($sessionId);
        }

        if ($this->understanding->isGreeting($message)) {
            return $this->responseBuilder->welcome($sessionId);
        }

        $currentState = $this->state->get($sessionId);
        $intent = $this->understanding->understand($message, $currentState);
        $this->state->rememberMessage($sessionId, 'user', $message);

        if ($intent['needs']['city'] || $intent['needs']['service']) {
            $this->state->save($sessionId, $intent['state']);

            return $this->responseBuilder->clarification($sessionId, $intent);
        }

        $providers = $this->search->search($intent);
        $intent['state']['last_results_ids'] = $providers->pluck('id')->all();
        $this->state->save($sessionId, $intent['state']);

        $context = $this->contextBuilder->build($message, $intent, $providers, $this->state->messages($sessionId));
        $aiMessage = $this->deepSeek->formatReply($context);

        return $this->responseBuilder->results(
            sessionId: $sessionId,
            intent: $intent,
            providers: $providers,
            aiMessage: $aiMessage,
        );
    }
}
