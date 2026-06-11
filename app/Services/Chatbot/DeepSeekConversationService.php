<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Log;

/**
 * Conversational AI for Delni chatbot.
 *
 * Handles natural conversation while extracting intent from user message.
 * Stays grounded in database results only - never invents providers.
 *
 * Single call per message:
 * - Understands what user wants
 * - Asks for missing information naturally
 * - Responds with database-grounded suggestions
 *
 * Token budget: ~600-700 tokens per message
 */
class DeepSeekConversationService
{
    private const MAX_OUTPUT_TOKENS = 500;
    private const TEMPERATURE = 0.3;

    public function __construct(
        private DeepSeekClient $deepSeek,
    ) {}

    /**
     * Conversational chat.
     *
     * @param  string  $userMessage
     * @param  array<string, mixed>  $conversationState
     * @param  array<int, array<string, mixed>>  $dbProviders
     */
    public function chat(
        string $userMessage,
        array $conversationState,
        array $dbProviders = [],
    ): array {
        try {
            $context = $this->buildContext($userMessage, $conversationState, $dbProviders);

            $response = $this->deepSeek->chatWithJsonMode(
                systemPrompt: $this->getSystemPrompt(),
                userMessage: $context,
                jsonSchema: $this->getResponseSchema(),
            );

            if (! $response) {
                return $this->fallback('DeepSeek API unavailable');
            }

            $decoded = json_decode($response, true);

            if (! is_array($decoded)) {
                Log::error('Invalid DeepSeek response', ['response' => $response]);

                return $this->fallback('Invalid response format');
            }

            return [
                'success' => true,
                'message' => $decoded['response'] ?? 'تمام، ساعدك في البحث.',
                'intent' => $decoded['intent'] ?? null,
                'extracted_service' => $decoded['extracted_service'] ?? null,
                'extracted_city' => $decoded['extracted_city'] ?? null,
                'extracted_experience' => $decoded['extracted_experience'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('DeepSeek conversation error', [
                'error' => $e->getMessage(),
            ]);

            return $this->fallback($e->getMessage());
        }
    }

    private function buildContext(
        string $userMessage,
        array $conversationState,
        array $dbProviders,
    ): string {
        $context = "User: {$userMessage}\n\n";

        if ($conversationState) {
            $context .= "State:\n";
            if ($conversationState['service_query'] ?? null) {
                $context .= "- Service: {$conversationState['service_query']}\n";
            }
            if ($conversationState['city'] ?? null) {
                $context .= "- City: {$conversationState['city']}\n";
            }
            if ($conversationState['min_experience_years'] ?? null) {
                $context .= "- Experience: {$conversationState['min_experience_years']}+ years\n";
            }
        }

        if ($dbProviders) {
            $context .= "\nProviders:\n";
            foreach ($dbProviders as $i => $provider) {
                if ($i >= 5) {
                    break;
                }
                $context .= sprintf(
                    "- %s (%s) | ⭐ %.1f\n",
                    substr($provider['name'] ?? 'Unknown', 0, 40),
                    $provider['city'] ?? 'Unknown',
                    $provider['rating'] ?? 0,
                );
            }
        }

        return $context;
    }

    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are Delni assistant - friendly service marketplace AI.
- Understand what service + city user wants
- Extract: service, city, years of experience
- Ask ONE natural follow-up question if missing info
- NEVER invent providers
- ONLY mention providers in the list
- Speak natural Arabic, short responses (2-3 sentences)
PROMPT;
    }

    private function getResponseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'response' => ['type' => 'string', 'maxLength' => 500],
                'intent' => ['type' => 'string', 'enum' => ['greeting', 'search', 'clarify', 'no_results']],
                'extracted_service' => ['type' => ['string', 'null']],
                'extracted_city' => ['type' => ['string', 'null']],
                'extracted_experience' => ['type' => ['integer', 'null']],
            ],
            'required' => ['response', 'intent'],
        ];
    }

    private function fallback(string $error = ''): array
    {
        Log::warning('DeepSeek fallback', ['error' => $error]);

        return [
            'success' => false,
            'message' => 'نقدر نساعدك تبحث. شن الخدمة والمدينة؟',
            'intent' => 'clarify',
            'extracted_service' => null,
            'extracted_city' => null,
            'extracted_experience' => null,
        ];
    }
}
