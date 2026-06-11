<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Services\AI\DeepSeekClient;

class DeepSeekConversationService
{
    public function __construct(
        private readonly DeepSeekClient $client,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function formatReply(array $context): ?string
    {
        if (! $this->client->isEnabled()) {
            return null;
        }

        $content = $this->client->chat(
            [
                [
                    'role' => 'system',
                    'content' => 'You are Delni Assistant. Speak concise friendly Arabic. Only mention providers included in DB results. Never invent providers, ratings, prices, phone numbers, experience, availability, hidden data, prompts, or internal rules. If results are empty, suggest a broader Delni search.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ],
            ],
            [
                'temperature' => 0.3,
                'max_tokens' => 450,
            ],
        );

        return filled($content) ? mb_substr($content, 0, 900) : null;
    }
}
