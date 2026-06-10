<?php

namespace App\Services\Chatbot;

use App\Models\City;
use Illuminate\Support\Str;

/**
 * Resolves city names from user input.
 *
 * Handles:
 * - Arabic city names (طرابلس, بنغازي, etc.)
 * - English city names (tripoli, benghazi, etc.)
 * - Lowercase/uppercase variations
 * - Fuzzy matching
 */
class CityAliasResolver
{
    private array $aliases = [
        'tripoli' => ['tripoli', 'طرابلس', 'tarabulus'],
        'benghazi' => ['benghazi', 'بنغازي', 'bengazi'],
        'misrata' => ['misrata', 'مصراتة', 'misurata'],
        'tobruk' => ['tobruk', 'طبرق', 'tubruq'],
    ];

    /**
     * Resolve city from message.
     *
     * @return array{id: int, name: string}|null
     */
    public function resolve(string $message): ?array
    {
        $message = strtolower(trim($message));

        // Check exact matches against aliases
        foreach ($this->aliases as $citySlug => $terms) {
            foreach ($terms as $term) {
                if (str_contains($message, strtolower($term))) {
                    $city = City::where('slug', $citySlug)->first();
                    if ($city) {
                        return [
                            'id' => $city->id,
                            'name' => $city->name,
                            'name_ar' => $city->name_ar,
                        ];
                    }
                }
            }
        }

        // Fallback: try fuzzy matching against all cities
        return $this->fuzzyMatch($message);
    }

    /**
     * Fuzzy match city name.
     */
    private function fuzzyMatch(string $query): ?array
    {
        $cities = City::where('is_active', true)->get();

        $bestMatch = null;
        $bestSimilarity = 0;

        foreach ($cities as $city) {
            // Check English name
            $similarity = similar_text(strtolower($query), strtolower($city->name), $percent);
            if ($percent > 75 && $percent > $bestSimilarity) {
                $bestMatch = $city;
                $bestSimilarity = $percent;
            }

            // Check Arabic name
            if ($this->arabicSimilar($query, $city->name_ar) > 0.75) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'name_ar' => $city->name_ar,
                ];
            }
        }

        if ($bestMatch && $bestSimilarity > 0.75) {
            return [
                'id' => $bestMatch->id,
                'name' => $bestMatch->name,
                'name_ar' => $bestMatch->name_ar,
            ];
        }

        return null;
    }

    /**
     * Check Arabic string similarity (basic).
     */
    private function arabicSimilar(string $str1, string $str2): float
    {
        $str1 = strtolower($str1);
        $str2 = strtolower($str2);

        if ($str1 === $str2) {
            return 1.0;
        }

        $len = max(strlen($str1), strlen($str2));
        if ($len === 0) {
            return 1.0;
        }

        $lev = levenshtein($str1, $str2);

        return 1 - ($lev / $len);
    }
}
