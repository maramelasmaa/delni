<?php

namespace App\Services\Chatbot;

use App\Models\Category;
use App\Models\City;
use Illuminate\Support\Collection;

/**
 * Intelligently matches providers to user service needs.
 *
 * Uses intent extraction + scoring + fallback strategies
 * to find the best providers without asking users about categories.
 */
class SmartProviderMatcher
{
    public function __construct(
        private ServiceIntentExtractor $intentExtractor,
        private ProviderMatchScorer $scorer,
        private ProviderSearchForChatService $providerSearch,
    ) {}

    /**
     * Find best matching providers for a service request.
     *
     * @return array{
     *   providers: Collection,
     *   needs_city: bool,
     *   needs_clarification: bool,
     *   clarification_message: string|null,
     *   fallback_explanation: string|null,
     *   intent: array,
     * }
     */
    public function match(string $message): array
    {
        $intent = $this->intentExtractor->extract($message);

        // If no service detected, ask for clarification
        if ($intent['confidence'] === 'low' || ! $intent['category_slug']) {
            return [
                'providers' => collect(),
                'needs_city' => false,
                'needs_clarification' => true,
                'clarification_message' => 'شنو نوع الخدمة اللي تبحث عنها؟',
                'fallback_explanation' => null,
                'intent' => $intent,
            ];
        }

        // If city is needed but not provided, ask for it
        if (! $intent['city_id'] && $this->isCityRequired($intent)) {
            return [
                'providers' => collect(),
                'needs_city' => true,
                'needs_clarification' => false,
                'clarification_message' => 'في أي مدينة تبحث عن ' . $intent['service'] . '؟',
                'fallback_explanation' => null,
                'intent' => $intent,
            ];
        }

        // Search for providers using intent
        $providers = $this->providerSearch->search(
            categoryId: $this->getCategoryId($intent['category_slug']),
            cityId: $intent['city_id'],
            experienceYears: $intent['experience_years_min'],
            remoteOnly: $intent['remote_preferred'],
        );

        // If no exact match, try fallbacks
        if ($providers->isEmpty()) {
            return $this->handleNoResults($intent);
        }

        // Score and sort providers
        $scoredProviders = $this->scorer->score($providers, $intent);

        // Filter to top results
        $topProviders = collect($scoredProviders)
            ->take(5)
            ->map(fn ($item) => $item['provider'])
            ->values();

        return [
            'providers' => $topProviders,
            'needs_city' => false,
            'needs_clarification' => false,
            'clarification_message' => null,
            'fallback_explanation' => null,
            'intent' => $intent,
        ];
    }

    /**
     * Handle case when no providers match exactly.
     * Try fallback strategies.
     */
    private function handleNoResults(array $intent): array
    {
        // Fallback 1: Same service, different/no city filter
        if ($intent['city_id']) {
            $providers = $this->providerSearch->search(
                categoryId: $this->getCategoryId($intent['category_slug']),
                remoteOnly: $intent['remote_preferred'],
            );

            if ($providers->isNotEmpty()) {
                return [
                    'providers' => $providers->take(5),
                    'needs_city' => false,
                    'needs_clarification' => false,
                    'clarification_message' => null,
                    'fallback_explanation' => 'ما لقيتش نتائج في ' . $intent['city'] . '، لكن لقيت نتائج في مدن أخرى:',
                    'intent' => $intent,
                ];
            }
        }

        // Fallback 2: Try without experience requirement
        if ($intent['experience_years_min']) {
            $providers = $this->providerSearch->search(
                categoryId: $this->getCategoryId($intent['category_slug']),
                cityId: $intent['city_id'],
                remoteOnly: $intent['remote_preferred'],
            );

            if ($providers->isNotEmpty()) {
                return [
                    'providers' => $providers->take(5),
                    'needs_city' => false,
                    'needs_clarification' => false,
                    'clarification_message' => null,
                    'fallback_explanation' => 'ما لقيتش بخبرة ' . $intent['experience_years_min'] . ' سنين، لكن لقيت نتائج مطابقة:',
                    'intent' => $intent,
                ];
            }
        }

        // No results at all
        return [
            'providers' => collect(),
            'needs_city' => false,
            'needs_clarification' => false,
            'clarification_message' => null,
            'fallback_explanation' => 'حالياً ما لقيناش مقدم خدمة مطابق. تقدر تجرب مدينة ثانية أو وصف مختلف.',
            'intent' => $intent,
        ];
    }

    private function isCityRequired(array $intent): bool
    {
        // These services are location-based
        $locationBasedServices = [
            'hvac-air-conditioning',
            'construction-contracting',
            'plumbing-services',
            'electrical-services',
        ];

        return in_array($intent['category_slug'], $locationBasedServices);
    }

    private function getCategoryId(string $slug): ?int
    {
        return Category::where('slug', $slug)->value('id');
    }
}
