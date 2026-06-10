<?php

namespace App\Services\Chatbot;

use App\Data\ProviderChatResultDTO;
use Illuminate\Support\Collection;

/**
 * Scores and ranks providers based on match relevance.
 *
 * Scoring factors:
 * - Exact category/subcategory match
 * - City match
 * - Experience years (if requested)
 * - Remote capability (if requested)
 * - Rating and reviews
 * - Keyword match in bio/business name
 */
class ProviderMatchScorer
{
    public function score(
        Collection $providers,
        array $intent,
    ): Collection {
        return $providers
            ->map(fn (ProviderChatResultDTO $provider) => $this->scoreProvider($provider, $intent))
            ->sortByDesc('score')
            ->values();
    }

    /**
     * Score a single provider against intent.
     *
     * @return array{provider: ProviderChatResultDTO, score: int, match_reason: string, fallback_level: string}
     */
    private function scoreProvider(ProviderChatResultDTO $provider, array $intent): array
    {
        $score = 0;
        $matchReasons = [];
        $fallbackLevel = 'partial';

        // City match (high weight)
        if ($intent['city_id'] && $provider->cityId === $intent['city_id']) {
            $score += 40;
            $matchReasons[] = 'city';
        } elseif ($intent['city_id']) {
            $score -= 10;
        }

        // Experience match (if requested)
        if ($intent['experience_years_min'] && $provider->experienceYears) {
            if ($provider->experienceYears >= $intent['experience_years_min']) {
                $score += 30;
                $matchReasons[] = 'experience';
                $fallbackLevel = 'exact';
            } else {
                $yearsDiff = $intent['experience_years_min'] - $provider->experienceYears;
                $score += max(0, 30 - ($yearsDiff * 5));
                $matchReasons[] = "experience_fallback_{$yearsDiff}";
            }
        }

        // Remote preference match
        if ($intent['remote_preferred'] && $provider->offersRemoteWork) {
            $score += 25;
            $matchReasons[] = 'remote';
            $fallbackLevel = 'exact';
        } elseif ($intent['remote_preferred']) {
            $score -= 5;
        }

        // Rating and reviews (medium weight)
        if ($provider->ratingAvg) {
            $score += min(25, (int) ($provider->ratingAvg * 4));
        }
        if ($provider->reviewsCount > 0) {
            $score += min(10, (int) ($provider->reviewsCount / 5));
        }

        // Keyword match in bio/business name
        if ($intent['service_original']) {
            $bio = strtolower($provider->bio ?? '');
            $name = strtolower($provider->businessName ?? '');
            $service = strtolower($intent['service_original']);

            if (str_contains($bio, $service) || str_contains($name, $service)) {
                $score += 15;
                $matchReasons[] = 'keyword';
            }
        }

        // Quality preference
        if ($intent['quality_hint'] === 'محترف' || $intent['quality_hint'] === 'خبير') {
            if ($provider->ratingAvg && $provider->ratingAvg >= 4) {
                $score += 10;
            }
        }

        $matchReason = match (true) {
            in_array('city', $matchReasons) && in_array('experience', $matchReasons) => 'مطابق كامل للمدينة والخبرة',
            in_array('city', $matchReasons) => 'مطابق للمدينة',
            in_array('experience', $matchReasons) => 'مطابق للخبرة المطلوبة',
            in_array('remote', $matchReasons) => 'متاح للعمل عن بعد',
            in_array('keyword', $matchReasons) => 'تخصص مناسب',
            default => 'نتيجة متاحة',
        };

        return [
            'provider' => $provider,
            'score' => max(0, $score),
            'match_reason' => $matchReason,
            'fallback_level' => $fallbackLevel,
        ];
    }
}
