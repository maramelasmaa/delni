<?php

namespace App\Services\Chatbot;

/**
 * Extracts structured service intent from natural Arabic messages.
 *
 * Identifies:
 * - Service type (تكييف, محامي, مقاول, etc.)
 * - Profession hints
 * - City name
 * - Experience requirements
 * - Remote preferences
 * - Budget/payment hints
 * - Quality indicators
 *
 * Returns confidence scores to guide whether clarification is needed.
 */
class ServiceIntentExtractor
{
    public function __construct(
        private CityResolverService $cityResolver,
    ) {}

    /**
     * Extract intent from user message.
     *
     * @return array{
     *   service: string,
     *   service_original: string,
     *   profession_hint: string|null,
     *   city: string|null,
     *   city_id: int|null,
     *   experience_years_min: int|null,
     *   remote_preferred: bool,
     *   budget_hint: string|null,
     *   urgency_hint: string|null,
     *   quality_hint: string|null,
     *   category_slug: string|null,
     *   confidence: string,
     * }
     */
    public function extract(string $message): array
    {
        $message = trim($message);
        $aliases = config('delni_service_aliases', []);

        // Find service by alias matching
        $detectedService = $this->detectService($message, $aliases);

        // Extract auxiliary info
        $city = $this->extractCity($message);
        $experience = $this->extractExperience($message);
        $profession = $this->extractProfession($message);
        $remote = $this->detectRemotePreference($message);
        $budget = $this->extractBudgetHint($message);
        $urgency = $this->extractUrgency($message);
        $quality = $this->extractQuality($message);

        return [
            'service' => $detectedService['normalized'] ?? $message,
            'service_original' => $message,
            'profession_hint' => $profession,
            'city' => $city['name'] ?? null,
            'city_id' => $city['id'] ?? null,
            'experience_years_min' => $experience,
            'remote_preferred' => $remote,
            'budget_hint' => $budget,
            'urgency_hint' => $urgency,
            'quality_hint' => $quality,
            'category_slug' => $detectedService['category_slug'] ?? null,
            'confidence' => $detectedService['confidence'] ?? 'low',
        ];
    }

    private function detectService(string $message, array $aliases): array
    {
        foreach ($aliases as $serviceKey => $config) {
            foreach ($config['aliases'] as $alias) {
                if (str_contains($message, $alias)) {
                    return [
                        'normalized' => $alias,
                        'category_slug' => $config['category_slug'],
                        'confidence' => $config['confidence'] ?? 'high',
                    ];
                }
            }
        }

        return ['normalized' => null, 'category_slug' => null, 'confidence' => 'low'];
    }

    private function extractCity(string $message): array
    {
        $cities = $this->cityResolver->extractFromMessage($message);

        if (empty($cities)) {
            return [];
        }

        $firstCity = $cities[0];

        return [
            'name' => $firstCity['name'] ?? null,
            'id' => $firstCity['id'] ?? null,
        ];
    }

    private function extractExperience(string $message): ?int
    {
        // Match patterns like "10 سنين خبرة", "بخبرة 15 سنة"
        if (preg_match('/(\d+)\s*(?:سنين|سنة|سنوات)\s*(?:خبرة)?/u', $message, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function extractProfession(string $message): ?string
    {
        // Extract profession hints: فني, مهندس, مصور, etc.
        $professions = ['فني', 'مهندس', 'مصور', 'محامي', 'مقاول', 'مصمم', 'سباك', 'كهربائي'];

        foreach ($professions as $profession) {
            if (str_contains($message, $profession)) {
                return $profession;
            }
        }

        return null;
    }

    private function detectRemotePreference(string $message): bool
    {
        $remoteIndicators = ['عن بعد', 'عن بعيد', 'اونلاين', 'أونلاين', 'عبر الإنترنت'];

        foreach ($remoteIndicators as $indicator) {
            if (str_contains($message, $indicator)) {
                return true;
            }
        }

        return false;
    }

    private function extractBudgetHint(string $message): ?string
    {
        $budgetTerms = ['تقسيط', 'دفع', 'سعر', 'تكلفة', 'أرخص', 'بأقل', 'ميزانية'];

        foreach ($budgetTerms as $term) {
            if (str_contains($message, $term)) {
                return $term;
            }
        }

        return null;
    }

    private function extractUrgency(string $message): ?string
    {
        $urgentTerms = ['سريع', 'بسرعة', 'ضروري', 'طارئ', 'عاجل', 'الآن'];

        foreach ($urgentTerms as $term) {
            if (str_contains($message, $term)) {
                return $term;
            }
        }

        return null;
    }

    private function extractQuality(string $message): ?string
    {
        $qualityTerms = ['أفضل', 'محترف', 'خبير', 'متخصص', 'معروف', 'موثوق'];

        foreach ($qualityTerms as $term) {
            if (str_contains($message, $term)) {
                return $term;
            }
        }

        return null;
    }
}
