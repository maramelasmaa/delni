<?php

namespace App\Services\Chatbot;

use App\Data\ProviderChatResultDTO;
use Illuminate\Support\Collection;

/**
 * Builds safe context for chatbot prompt.
 *
 * Only includes:
 * - Intent and user preferences
 * - Public provider information
 * - City and category filters
 *
 * Never includes:
 * - Full database models
 * - Email addresses
 * - Admin/internal fields
 * - Hidden or suspended providers
 */
class ChatContextBuilderService
{
    /**
     * Build context for DeepSeek prompt.
     *
     * @param  Collection<int, ProviderChatResultDTO>  $providers
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function build(
        string $intent,
        ?int $categoryId = null,
        ?int $subcategoryId = null,
        ?int $cityId = null,
        Collection $providers = null,
        array $metadata = [],
    ): array {
        $providers ??= collect();

        // Limit to top 5 providers
        $providersData = $providers
            ->take(5)
            ->map(fn (ProviderChatResultDTO $dto) => [
                'name' => $dto->businessName,
                'city' => $dto->city,
                'category' => $dto->category,
                'rating' => $dto->ratingAvg,
                'reviews_count' => $dto->reviewsCount,
                'badges' => $this->buildBadges($dto),
            ])
            ->toArray();

        return [
            'intent' => $intent,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'city_id' => $cityId,
            'providers_count' => count($providersData),
            'providers' => $providersData,
            'user_authenticated' => $metadata['user_authenticated'] ?? false,
            'conversation_turn' => $metadata['conversation_turn'] ?? 1,
        ];
    }

    /**
     * Build visual badges for provider display.
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
}
