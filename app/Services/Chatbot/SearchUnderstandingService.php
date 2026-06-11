<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Models\Category;
use App\Models\City;
use App\Models\Subcategory;
use App\Services\ArabicNormalizationService;
use Illuminate\Support\Str;

class SearchUnderstandingService
{
    public function __construct(
        private readonly ArabicNormalizationService $normalization,
    ) {}

    public function isGreeting(string $message): bool
    {
        $normalized = $this->normalization->normalize($message);

        return in_array($normalized, ['hi', 'hello', 'hey', 'مرحبا', 'السلام عليكم', 'اهلا', 'أهلا'], true);
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function understand(string $message, array $state): array
    {
        $normalized = $this->normalization->normalize($message);
        $city = $this->resolveCity($normalized);
        $category = $this->resolveCategory($normalized);
        $subcategory = $this->resolveSubcategory($normalized);
        $experience = $this->extractExperience($normalized);

        if ($city !== null) {
            $state['city_id'] = $city->id;
            $state['city_name'] = $city->name_ar ?: $city->name;
        }

        if ($category !== null) {
            $state['category_id'] = $category->id;
            $state['service_query'] = $category->name_ar ?: $category->name;
        }

        if ($subcategory !== null) {
            $state['subcategory_id'] = $subcategory->id;
            $state['category_id'] = $subcategory->category_id;
            $state['service_query'] = $subcategory->name_ar ?: $subcategory->name;
        }

        if ($experience !== null) {
            $state['min_experience_years'] = $experience;
        }

        $cleaned = $this->removeKnownWords($normalized, [
            $city?->name, $city?->name_ar,
            $category?->name, $category?->name_ar,
            $subcategory?->name, $subcategory?->name_ar,
        ]);

        if (($state['service_query'] ?? null) === null && $cleaned !== '') {
            $state['service_query'] = $cleaned;
        }

        if ($this->looksLikeProviderSearch($normalized, $state)) {
            $state['provider_name_query'] = $cleaned !== '' ? $cleaned : $normalized;
            $state['service_query'] = null;
        }

        $state['intent'] = 'provider_search';
        $state['pending_fields'] = $this->pendingFields($state);

        return [
            'type' => 'provider_search',
            'state' => $state,
            'query' => [
                'service' => $state['service_query'],
                'city_id' => $state['city_id'],
                'provider_name' => $state['provider_name_query'],
                'category_id' => $state['category_id'],
                'subcategory_id' => $state['subcategory_id'],
                'min_experience_years' => $state['min_experience_years'],
            ],
            'needs' => [
                'city' => in_array('city', $state['pending_fields'], true),
                'service' => in_array('service', $state['pending_fields'], true),
            ],
        ];
    }

    private function resolveCity(string $normalized): ?City
    {
        return City::query()
            ->where('is_active', true)
            ->get()
            ->first(fn (City $city): bool => $this->containsName($normalized, $city->name) || $this->containsName($normalized, $city->name_ar));
    }

    private function resolveCategory(string $normalized): ?Category
    {
        return Category::query()
            ->where('is_active', true)
            ->get()
            ->first(fn (Category $category): bool => $this->containsName($normalized, $category->name) || $this->containsName($normalized, $category->name_ar));
    }

    private function resolveSubcategory(string $normalized): ?Subcategory
    {
        return Subcategory::query()
            ->where('is_active', true)
            ->get()
            ->first(fn (Subcategory $subcategory): bool => $this->containsName($normalized, $subcategory->name) || $this->containsName($normalized, $subcategory->name_ar));
    }

    private function containsName(string $haystack, ?string $name): bool
    {
        $name = $this->normalization->normalize($name);

        return $name !== '' && str_contains($haystack, $name);
    }

    /**
     * @param  array<int, string|null>  $knownWords
     */
    private function removeKnownWords(string $message, array $knownWords): string
    {
        foreach ($knownWords as $word) {
            $word = $this->normalization->normalize($word);
            if ($word !== '') {
                $message = trim(str_replace($word, '', $message));
            }
        }

        $noise = ['نبي', 'نبى', 'ابي', 'أبي', 'اريد', 'بدور', 'وين نلقى', 'وين', 'نلقى', 'في', 'with', 'need', 'i need', 'years', 'experience', 'سنوات', 'خبرة'];

        return trim(str_replace($noise, '', $message));
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function looksLikeProviderSearch(string $normalized, array $state): bool
    {
        if (filled($state['provider_name_query'] ?? null)) {
            return true;
        }

        $tokens = preg_split('/\s+/u', trim($normalized)) ?: [];

        if (str_contains($normalized, 'شركة') || Str::contains(Str::lower($normalized), ['dr ', 'doctor ', 'company'])) {
            return true;
        }

        if (count($tokens) >= 2 && str_starts_with($normalized, 'فني ') && ! str_contains($normalized, 'تكييف')) {
            return true;
        }

        return false;
    }

    private function extractExperience(string $message): ?int
    {
        preg_match('/(\d{1,2})\s*(سن|years?|خبر)/iu', $message, $matches);

        return isset($matches[1]) ? (int) $matches[1] : null;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<int, string>
     */
    private function pendingFields(array $state): array
    {
        $pending = [];

        if (! filled($state['service_query'] ?? null) && ! filled($state['provider_name_query'] ?? null)) {
            $pending[] = 'service';
        }

        if (! filled($state['city_id'] ?? null) && filled($state['service_query'] ?? null) && ! filled($state['provider_name_query'] ?? null)) {
            $pending[] = 'city';
        }

        return $pending;
    }
}
