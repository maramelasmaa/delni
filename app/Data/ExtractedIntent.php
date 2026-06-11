<?php

namespace App\Data;

/**
 * Extracted intent from user message.
 *
 * Never contains provider data - only search intent.
 * Used to query the database for providers.
 */
class ExtractedIntent
{
    public function __construct(
        public ?string $specialty = null,
        public ?string $city = null,
        public ?string $genderPreference = null,
        public bool $budgetSensitive = false,
        public float $confidence = 0.0,
        public bool $needsClarification = false,
        public ?string $clarificationQuestion = null,
    ) {}

    /**
     * Create from parsed DeepSeek response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromParsed(array $data): self
    {
        return new self(
            specialty: $data['specialty'] ?? null,
            city: $data['city'] ?? null,
            genderPreference: $data['gender_preference'] ?? null,
            budgetSensitive: (bool) ($data['budget_sensitive'] ?? false),
            confidence: (float) ($data['confidence'] ?? 0.0),
            needsClarification: (bool) ($data['needs_clarification'] ?? false),
            clarificationQuestion: $data['clarification_question'] ?? null,
        );
    }

    /**
     * Create low-confidence/unclear result.
     */
    public static function unclear(): self
    {
        return new self(
            specialty: null,
            city: null,
            confidence: 0.0,
            needsClarification: true,
            clarificationQuestion: 'ممكن تشرح اكثر عن نوع الخدمة اللي تبحث عنها؟',
        );
    }

    /**
     * Is this extraction confident enough to search?
     */
    public function isConfident(): bool
    {
        // If specialty is clearly extracted, accept even at 0.50+ confidence
        if ($this->specialty && $this->confidence >= 0.50) {
            return true;
        }

        // Otherwise require higher confidence and no clarification flag
        return $this->confidence >= 0.70 && ! $this->needsClarification;
    }

    /**
     * Convert to array for response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'specialty' => $this->specialty,
            'city' => $this->city,
            'gender_preference' => $this->genderPreference,
            'budget_sensitive' => $this->budgetSensitive,
            'confidence' => round($this->confidence, 2),
            'needs_clarification' => $this->needsClarification,
            'clarification_question' => $this->clarificationQuestion,
        ];
    }
}
