<?php

namespace App\Services\Chatbot\Dialects;

/**
 * Normalize user input across dialects:
 * - Modern Standard Arabic (MSA)
 * - Libyan Colloquial Arabic
 * - English
 * - Mixed Arabic/English
 * - Arabizi (Arabic with numbers/Latin)
 * - Voice-to-text errors
 * - Misspellings
 *
 * Output: normalized string ready for intent extraction.
 */
class DialectNormalizer
{
    private ArabicNormalizer $arabicNormalizer;

    private ArabiziNormalizer $arabiziNormalizer;

    private SpellingCorrector $spellingCorrector;

    public function __construct()
    {
        $this->arabicNormalizer = new ArabicNormalizer;
        $this->arabiziNormalizer = new ArabiziNormalizer;
        $this->spellingCorrector = new SpellingCorrector;
    }

    /**
     * Normalize user message across all dialects.
     */
    public function normalize(string $input): string
    {
        $text = trim($input);

        // Step 1: Detect and convert Arabizi to Arabic
        if ($this->isArabizi($text)) {
            $text = $this->arabiziNormalizer->toArabic($text);
        }

        // Step 2: Normalize Arabic (diacritics, hamza variations)
        if ($this->containsArabic($text)) {
            $text = $this->arabicNormalizer->normalize($text);
        }

        // Step 3: Normalize English (lowercase, common contractions)
        $text = $this->normalizeEnglish($text);

        // Step 4: Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Detect if text is Arabizi (Arabic with numbers/Latin).
     */
    private function isArabizi(string $text): bool
    {
        // Arabizi patterns: 3 = ع, 7 = ح, 8 = ق, 9 = ق, 0 = ع
        $arabiziNumbers = preg_match('/[0-9].*[ا-ي]|[ا-ي].*[0-9]/', $text);

        // Mixed Latin+Arabic without spaces
        $mixedLatinArabic = preg_match('/[a-z][ا-ي]|[ا-ي][a-z]/', $text);

        return $arabiziNumbers || $mixedLatinArabic;
    }

    /**
     * Check if text contains Arabic.
     */
    private function containsArabic(string $text): bool
    {
        return preg_match('/[؀-ۿ]/u', $text) === 1;
    }

    /**
     * Normalize English portion.
     */
    private function normalizeEnglish(string $text): string
    {
        // Lowercase
        $text = strtolower($text);

        // Common contractions
        $text = str_replace(["don't", "doesn't"], 'do not', $text);
        $text = str_replace(["can't"], 'cannot', $text);
        $text = str_replace(["won't"], 'will not', $text);

        return $text;
    }
}
