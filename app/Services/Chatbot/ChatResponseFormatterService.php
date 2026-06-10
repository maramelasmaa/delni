<?php

namespace App\Services\Chatbot;

use App\Data\ProviderChatResultDTO;
use Illuminate\Support\Collection;

/**
 * Formats chatbot responses into structured JSON.
 *
 * Ensures consistent response format across all intents.
 */
class ChatResponseFormatterService
{
    /**
     * Format a successful response.
     *
     * @param  Collection<int, ProviderChatResultDTO>  $providers
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function format(
        string $message,
        string $intent,
        Collection $providers = null,
        array $metadata = [],
    ): array {
        $providers ??= collect();

        return [
            'message' => $message,
            'intent' => $intent,
            'session_id' => $metadata['session_id'] ?? null,
            'providers' => $this->formatProviders($providers),
            'suggested_actions' => $this->buildSuggestedActions($intent, $providers),
            'needs' => [
                'city' => $metadata['needs_city'] ?? false,
                'category' => $metadata['needs_category'] ?? false,
            ],
        ];
    }

    /**
     * Format provider data for response.
     *
     * @param  Collection<int, ProviderChatResultDTO>  $providers
     * @return array<int, array<string, mixed>>
     */
    private function formatProviders(Collection $providers): array
    {
        return $providers
            ->take(5)
            ->map(fn (ProviderChatResultDTO $dto) => [
                'id' => $dto->id,
                'name' => $dto->businessName,
                'slug' => $dto->slug,
                'city' => $dto->city,
                'category' => $dto->category,
                'rating' => $dto->ratingAvg,
                'reviews_count' => $dto->reviewsCount,
                'logo_url' => $dto->logoUrl,
                'badges' => $this->buildBadges($dto),
                'url' => route('public.provider.profile', $dto->slug),
            ])
            ->toArray();
    }

    /**
     * Build visual badges for provider.
     *
     * @return array<int, string>
     */
    private function buildBadges(ProviderChatResultDTO $dto): array
    {
        $badges = [];

        if ($dto->isFeatured) {
            $badges[] = 'مميز';
        }

        if ($dto->isTopRated) {
            $badges[] = 'الأفضل تقييماً';
        }

        if ($dto->offersRemoteWork) {
            $badges[] = 'عمل عن بعد';
        }

        return $badges;
    }

    /**
     * Build suggested actions for user.
     *
     * @param  Collection<int, ProviderChatResultDTO>  $providers
     * @return array<int, array<string, string>>
     */
    private function buildSuggestedActions(string $intent, Collection $providers): array
    {
        $actions = [];

        if ($intent === 'provider_search' && $providers->count() > 0) {
            $actions[] = [
                'label' => 'عرض المزيد',
                'url' => route('public.search'),
            ];
        }

        if ($intent === 'provider_join_question') {
            $actions[] = [
                'label' => 'تسجيل كمزود خدمات',
                'url' => route('register'),
            ];
        }

        return $actions;
    }
}
