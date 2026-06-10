<?php

namespace App\Services\Chatbot;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Collection;

/**
 * Resolves Arabic service terms to actual categories and subcategories.
 *
 * Supports:
 * - Exact name matching (Arabic & English)
 * - Fuzzy/similar matching for typos
 * - Common Libyan terminology
 * - Service type descriptions
 */
class CategoryResolverService
{
    /**
     * Mapping of common Arabic terms to category IDs.
     * Key = Arabic term, Value = category slug
     *
     * This mapping is built from actual DB categories and extended
     * with common Libyan service terminology.
     *
     * @var array<string, string>
     */
    private array $arabicTerms = [
        // HVAC
        'مكيف' => 'hvac-air-conditioning',
        'تكييف' => 'hvac-air-conditioning',
        'مكيفات' => 'hvac-air-conditioning',
        'تبريد' => 'hvac-air-conditioning',
        'تدفئة' => 'hvac-air-conditioning',

        // Plumbing
        'سباك' => 'plumbing-services',
        'سباكة' => 'plumbing-services',
        'صرف' => 'plumbing-services',
        'أنابيب' => 'plumbing-services',

        // Electrical
        'كهربائي' => 'electrical-services',
        'كهربا' => 'electrical-services',
        'كهرباء' => 'electrical-services',
        'توصيل كهرباء' => 'electrical-services',

        // Law
        'محامي' => 'law-legal-services',
        'قانون' => 'law-legal-services',
        'محاماة' => 'law-legal-services',

        // Interior Design
        'ديكور' => 'interior-design-decoration',
        'تصميم داخلي' => 'interior-design-decoration',
        'دهانة' => 'interior-design-decoration',
        'أثاث' => 'interior-design-decoration',

        // Photography
        'تصوير' => 'photography-videography',
        'مصور' => 'photography-videography',
        'صور' => 'photography-videography',
        'تصوير عرس' => 'photography-videography',
        'تصوير أفراح' => 'photography-videography',
        'فيديو' => 'photography-videography',

        // Education/Tutoring
        'معلم' => 'education-tutoring',
        'دروس' => 'education-tutoring',
        'تعليم' => 'education-tutoring',
        'كورسات' => 'education-tutoring',

        // Beauty/Salon
        'حلاق' => 'beauty-salon-services',
        'صالون' => 'beauty-salon-services',
        'جمال' => 'beauty-salon-services',
        'تجميل' => 'beauty-salon-services',
        'مستحضرات تجميل' => 'beauty-salon-services',

        // Construction
        'بناء' => 'construction-contracting',
        'مقاول' => 'construction-contracting',
        'عمارة' => 'construction-contracting',
        'ترميم' => 'construction-contracting',

        // Car Services
        'سيارة' => 'automotive-car-services',
        'ميكانيكي' => 'automotive-car-services',
        'صيانة سيارات' => 'automotive-car-services',
        'تصليح سيارات' => 'automotive-car-services',
        'غسيل سيارات' => 'automotive-car-services',
    ];

    /**
     * Resolve a user's text input to category/subcategory.
     *
     * Returns an array with:
     * - category_id: resolved category (or null)
     * - subcategory_id: resolved subcategory (or null)
     * - confidence: 'high', 'medium', 'low'
     * - matched_term: the term that was matched
     *
     * @return array<string, mixed>|null
     */
    public function resolve(string $input): ?array
    {
        $input = trim(mb_strtolower($input, 'UTF-8'));

        if (! filled($input) || mb_strlen($input) < 2) {
            return null;
        }

        // Exact match on Arabic terms first
        $result = $this->matchArabicTerms($input);
        if ($result) {
            return $result;
        }

        // Try exact name matching on categories/subcategories
        $result = $this->matchExactNames($input);
        if ($result) {
            return $result;
        }

        // Try fuzzy/similar matching
        $result = $this->matchFuzzy($input);
        if ($result) {
            return $result;
        }

        return null;
    }

    /**
     * Get all active categories for chatbot.
     *
     * @return Collection<int, Category>
     */
    public function getAllCategories(): Collection
    {
        return Category::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get subcategories for a specific category.
     *
     * @return Collection<int, Subcategory>
     */
    public function getSubcategoriesForCategory(int $categoryId): Collection
    {
        return Subcategory::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Match against predefined Arabic term mapping.
     *
     * @return array<string, mixed>|null
     */
    private function matchArabicTerms(string $input): ?array
    {
        foreach ($this->arabicTerms as $term => $slug) {
            if ($input === mb_strtolower($term, 'UTF-8')) {
                $category = Category::where('slug', $slug)->first();
                if ($category) {
                    return [
                        'category_id' => $category->id,
                        'subcategory_id' => null,
                        'confidence' => 'high',
                        'matched_term' => $term,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Try exact name matching on category and subcategory names.
     *
     * @return array<string, mixed>|null
     */
    private function matchExactNames(string $input): ?array
    {
        // Check category names
        $category = Category::where('is_active', true)
            ->where(function ($q) use ($input): void {
                $q->whereRaw('LOWER(name) = ?', [$input])
                    ->orWhereRaw('LOWER(name_ar) = ?', [$input]);
            })
            ->first();

        if ($category) {
            return [
                'category_id' => $category->id,
                'subcategory_id' => null,
                'confidence' => 'high',
                'matched_term' => $input,
            ];
        }

        // Check subcategory names
        $subcategory = Subcategory::where('is_active', true)
            ->where(function ($q) use ($input): void {
                $q->whereRaw('LOWER(name) = ?', [$input])
                    ->orWhereRaw('LOWER(name_ar) = ?', [$input]);
            })
            ->first();

        if ($subcategory) {
            return [
                'category_id' => $subcategory->category_id,
                'subcategory_id' => $subcategory->id,
                'confidence' => 'high',
                'matched_term' => $input,
            ];
        }

        return null;
    }

    /**
     * Try fuzzy matching for typos or partial matches.
     *
     * @return array<string, mixed>|null
     */
    private function matchFuzzy(string $input): ?array
    {
        // Check all categories for similar names
        $categories = Category::where('is_active', true)->get();

        foreach ($categories as $category) {
            $similarity = $this->calculateSimilarity($input, mb_strtolower($category->name, 'UTF-8'));
            if ($similarity > 0.75) {
                return [
                    'category_id' => $category->id,
                    'subcategory_id' => null,
                    'confidence' => 'medium',
                    'matched_term' => $category->name,
                ];
            }

            $similarity = $this->calculateSimilarity($input, mb_strtolower($category->name_ar, 'UTF-8'));
            if ($similarity > 0.75) {
                return [
                    'category_id' => $category->id,
                    'subcategory_id' => null,
                    'confidence' => 'medium',
                    'matched_term' => $category->name_ar,
                ];
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
