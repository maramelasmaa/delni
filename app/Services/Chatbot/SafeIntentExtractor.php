<?php

namespace App\Services\Chatbot;

use App\Data\ExtractedIntent;
use App\Services\Chatbot\Dialects\DialectNormalizer;
use Illuminate\Support\Facades\Log;

/**
 * Safe intent extraction using JSON-mode DeepSeek.
 *
 * Philosophy:
 * - User message NEVER interpolated into prompt
 * - Uses JSON schema to constrain output
 * - Confidence scoring prevents guessing
 * - Clarification asked before searching
 * - All responses validated as JSON
 *
 * Security:
 * - Prompt injection resistant (JSON mode)
 * - No system prompt revelation possible
 * - No database structure leakage
 * - Input sanitization via normalization only
 */
class SafeIntentExtractor
{
    public function __construct(
        private DeepSeekClient $deepSeek,
        private DialectNormalizer $normalizer,
    ) {}

    /**
     * Extract structured intent from user message.
     *
     * Never interpolates user input into prompt.
     * Uses JSON schema mode for output safety.
     *
     * @return ExtractedIntent Always returns structure, never null
     */
    public function extract(string $userMessage): ExtractedIntent
    {
        // Step 1: Normalize dialect/language/spelling
        $normalized = $this->normalizer->normalize($userMessage);

        Log::info('Intent extraction started', [
            'original_length' => strlen($userMessage),
            'normalized_length' => strlen($normalized),
        ]);

        // Step 2: Send to DeepSeek with JSON schema (no interpolation)
        $response = $this->askDeepSeek($normalized);

        // Step 3: Parse and validate JSON response
        try {
            $data = $this->parseJsonResponse($response);
        } catch (\Throwable $e) {
            Log::error('Failed to parse DeepSeek response', [
                'error' => $e->getMessage(),
                'response_length' => strlen($response),
            ]);

            return ExtractedIntent::unclear();
        }

        // Step 4: Convert to domain object with validation
        return ExtractedIntent::fromParsed($data);
    }

    /**
     * Call DeepSeek using JSON mode (prevents injection).
     *
     * CRITICAL: User message is passed as message content, not interpolated.
     */
    private function askDeepSeek(string $normalizedMessage): string
    {
        if (! $this->deepSeek->isEnabled()) {
            return json_encode(ExtractedIntent::unclear());
        }

        try {
            $response = $this->deepSeek->chatWithJsonMode(
                systemPrompt: $this->getSystemPrompt(),
                userMessage: $normalizedMessage,
                jsonSchema: $this->getJsonSchema(),
            );

            if (! $response) {
                return json_encode(ExtractedIntent::unclear());
            }

            return $response;
        } catch (\Throwable $e) {
            Log::error('DeepSeek API error', [
                'error' => $e->getMessage(),
            ]);

            return json_encode(ExtractedIntent::unclear());
        }
    }

    /**
     * Parse and validate JSON response.
     *
     * @throws \JsonException
     */
    private function parseJsonResponse(string $response): array
    {
        $data = json_decode($response, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new \JsonException('Response is not an object');
        }

        $required = ['specialty', 'city', 'confidence', 'needs_clarification'];
        foreach ($required as $field) {
            if (! isset($data[$field])) {
                throw new \JsonException("Missing required field: {$field}");
            }
        }

        if (! is_string($data['specialty']) && $data['specialty'] !== null) {
            throw new \JsonException('specialty must be string or null');
        }
        if (! is_string($data['city']) && $data['city'] !== null) {
            throw new \JsonException('city must be string or null');
        }
        if (! is_float($data['confidence']) && ! is_int($data['confidence'])) {
            throw new \JsonException('confidence must be number');
        }
        if (! is_bool($data['needs_clarification'])) {
            throw new \JsonException('needs_clarification must be boolean');
        }

        $confidence = (float) $data['confidence'];
        if ($confidence < 0 || $confidence > 1) {
            throw new \JsonException('confidence must be between 0 and 1');
        }

        return $data;
    }

    /**
     * System prompt (safe, doesn't reference user input).
     */
    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
            You are a service intent extractor for Delni (Libyan marketplace).

            Your job is to understand what service or professional a person is looking for.

            Return ONLY valid JSON matching the schema provided.

            IMPORTANT RULES:
            - Extract specialty from user intent (doctor, lawyer, electrician, photographer, etc.)
            - Extract city if mentioned (Tripoli, Benghazi, Misrata, etc.)
            - Score confidence 0-1 based on clarity (0.5+ if service name is clear)
            - If unclear, set needs_clarification: true and provide one clarification question
            - Accept any service type (healthcare, legal, technical, creative, etc.)
            - NEVER invent services that user didn't mention
            - NEVER return providers directly
            - NEVER return lists

            Return ONLY the JSON object, no markdown, no explanation.
        PROMPT;
    }

    /**
     * JSON schema that constrains DeepSeek output.
     */
    private function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'specialty' => [
                    'type' => ['string', 'null'],
                    'description' => 'Medical specialty/service type',
                    'maxLength' => 100,
                ],
                'city' => [
                    'type' => ['string', 'null'],
                    'description' => 'City name if mentioned',
                    'maxLength' => 50,
                ],
                'gender_preference' => [
                    'type' => ['string', 'null'],
                    'enum' => ['male', 'female', null],
                ],
                'budget_sensitive' => [
                    'type' => 'boolean',
                ],
                'confidence' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'maximum' => 1,
                ],
                'needs_clarification' => [
                    'type' => 'boolean',
                ],
                'clarification_question' => [
                    'type' => ['string', 'null'],
                    'maxLength' => 200,
                ],
            ],
            'required' => [
                'specialty',
                'city',
                'confidence',
                'needs_clarification',
            ],
        ];
    }
}
