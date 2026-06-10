<?php

namespace App\Services\Chatbot;

use App\Models\City;
use Illuminate\Support\Collection;

/**
 * Resolves city names from user input.
 *
 * Supports:
 * - Exact city name matching
 * - Arabic city name matching
 * - Case-insensitive lookup
 * - Fuzzy matching for typos
 */
class CityResolverService
{
    /**
     * Resolve a city from text input.
     *
     * Returns array with:
     * - city_id: resolved city (or null)
     * - confidence: 'high', 'medium', 'low'
     * - matched_name: the city name that was matched
     *
     * @return array<string, mixed>|null
     */
    public function resolve(string $input): ?array
    {
        $input = trim(mb_strtolower($input, 'UTF-8'));

        if (! filled($input) || mb_strlen($input) < 2) {
            return null;
        }

        // Try exact match first
        $city = City::where('is_active', true)
            ->where(function ($q) use ($input): void {
                $q->whereRaw('LOWER(name) = ?', [$input])
                    ->orWhereRaw('LOWER(name_ar) = ?', [$input]);
            })
            ->first();

        if ($city) {
            return [
                'city_id' => $city->id,
                'confidence' => 'high',
                'matched_name' => $city->name,
            ];
        }

        // Try fuzzy matching
        return $this->matchFuzzy($input);
    }

    /**
     * Get all active cities for chatbot.
     *
     * @return Collection<int, City>
     */
    public function getAllCities(): Collection
    {
        return City::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Extract city names from a message using keyword matching.
     *
     * Returns array of possible city matches in order of confidence.
     *
     * @return array<int, array<string, mixed>>
     */
    public function extractFromMessage(string $message): array
    {
        $message = mb_strtolower($message, 'UTF-8');
        $cities = $this->getAllCities();
        $matches = [];

        foreach ($cities as $city) {
            $names = [
                mb_strtolower($city->name, 'UTF-8'),
                mb_strtolower($city->name_ar ?? '', 'UTF-8'),
            ];

            foreach ($names as $name) {
                if (filled($name) && str_contains($message, $name)) {
                    $matches[] = [
                        'city_id' => $city->id,
                        'name' => $city->name,
                        'confidence' => 'high',
                    ];
                    break;
                }
            }
        }

        return array_values(array_unique($matches, SORT_REGULAR));
    }

    /**
     * Try fuzzy matching for typos or partial matches.
     *
     * @return array<string, mixed>|null
     */
    private function matchFuzzy(string $input): ?array
    {
        $cities = City::where('is_active', true)->get();

        foreach ($cities as $city) {
            $similarity = $this->calculateSimilarity($input, mb_strtolower($city->name, 'UTF-8'));
            if ($similarity > 0.75) {
                return [
                    'city_id' => $city->id,
                    'confidence' => 'medium',
                    'matched_name' => $city->name,
                ];
            }

            if (filled($city->name_ar)) {
                $similarity = $this->calculateSimilarity($input, mb_strtolower($city->name_ar, 'UTF-8'));
                if ($similarity > 0.75) {
                    return [
                        'city_id' => $city->id,
                        'confidence' => 'medium',
                        'matched_name' => $city->name_ar,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Calculate string similarity using Levenshtein distance.
     *
     * Returns 0.0 to 1.0 where 1.0 is identical.
     */
    private function calculateSimilarity(string $a, string $b): float
    {
        $maxLen = max(mb_strlen($a, 'UTF-8'), mb_strlen($b, 'UTF-8'));
        if ($maxLen === 0) {
            return 1.0;
        }

        $distance = levenshtein($a, $b);

        return 1.0 - ($distance / $maxLen);
    }
}
