<?php

namespace App\Services;

/**
 * Arabic text normalization for search and indexing.
 *
 * Normalizes both searchable content and search queries so users can find providers
 * regardless of:
 * - Hamza placement (أ، إ، آ ← ا)
 * - Diacritics/tashkeel (ِ، َ، ُ removed)
 * - Final ta variants (ة ← ه)
 * - Ya/alef variants (ى ← ي)
 * - Extra spaces
 * - Case sensitivity
 *
 * Critical rule: Normalize BOTH indexed content AND search queries.
 * Never normalize only one side.
 *
 * Examples:
 *   "أخصائي أسنان" → "اخصائي اسنان"
 *   "تقنيّه" → "تقنيه"
 *   "احمــد" → "احمد"
 *   "الليبيّة" → "الليبيه"
 */
class ArabicNormalizationService
{
    /**
     * Normalize Arabic text for search indexing and query processing.
     *
     * This is the main normalization function — apply to both content and queries.
     *
     * @param  string|null  $text  The text to normalize
     * @return string The normalized text (or empty string if input null)
     */
    public function normalize(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // 1. Remove all diacritical marks (tashkeel)
        $text = $this->removeDiacritics($text);

        // 2. Normalize hamza variants: أ إ آ ٱ → ا
        $text = $this->normalizeHamza($text);

        // 3. Normalize ta variants: ة → ه
        $text = $this->normalizeTa($text);

        // 4. Normalize alef/ya variants: ى → ي
        $text = $this->normalizeAlefYa($text);

        // 5. Remove tatweel (Arabic letter stretching)
        $text = $this->removeTatweel($text);

        // 6. Normalize English characters to lowercase
        $text = mb_strtolower($text, 'UTF-8');

        // 7. Trim and normalize whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));

        // 8. Remove invisible Unicode characters
        $text = $this->removeInvisibleCharacters($text);

        return $text;
    }

    /**
     * Remove Arabic diacritical marks (tashkeel).
     *
     * Removes: fatha, damma, kasra, sukun, shadda, etc.
     * Uses regex pattern to match Unicode diacritical marks.
     */
    private function removeDiacritics(string $text): string
    {
        // Unicode regex pattern for Arabic diacritical marks
        // Covers: \p{Mn} = Mark, Nonspacing (combining marks)
        $text = preg_replace('/\p{Mn}/u', '', $text);

        return $text;
    }

    /**
     * Normalize hamza variants to base alef.
     *
     * أ (alef with madda above) → ا
     * إ (alef with hamza below) → ا
     * آ (alef with madda) → ا
     * ٱ (alef wasla) → ا
     */
    private function normalizeHamza(string $text): string
    {
        $text = str_replace('أ', 'ا', $text);
        $text = str_replace('إ', 'ا', $text);
        $text = str_replace('آ', 'ا', $text);
        $text = str_replace('ٱ', 'ا', $text);

        return $text;
    }

    /**
     * Normalize ta variants.
     *
     * ة (ta marbuta) → ه (ha)
     * This is consistent with common Libyan Arabic written form.
     */
    private function normalizeTa(string $text): string
    {
        return str_replace('ة', 'ه', $text);
    }

    /**
     * Normalize alef/ya variants.
     *
     * ى (alef maksura) → ي (ya)
     * This handles words written with either form.
     */
    private function normalizeAlefYa(string $text): string
    {
        return str_replace('ى', 'ي', $text);
    }

    /**
     * Remove tatweel (Arabic letter stretching).
     *
     * ـ (Arabic tatweel/kashida) used to stretch letters aesthetically.
     * Should be removed for search consistency.
     */
    private function removeTatweel(string $text): string
    {
        return str_replace('ـ', '', $text);
    }

    /**
     * Remove invisible Unicode characters that could confuse search.
     *
     * Zero-width joiner, zero-width non-joiner, etc.
     */
    private function removeInvisibleCharacters(string $text): string
    {
        return str_replace([
            "\u{200B}", // ZERO WIDTH SPACE
            "\u{200C}", // ZERO WIDTH NON-JOINER
            "\u{200D}", // ZERO WIDTH JOINER
            "\u{200E}", // LEFT-TO-RIGHT MARK
            "\u{200F}", // RIGHT-TO-LEFT MARK
            "\u{FEFF}", // ZERO WIDTH NO-BREAK SPACE
        ], '', $text);
    }

    /**
     * Check if text contains Arabic characters.
     *
     * Useful for determining whether to apply Arabic normalization.
     */
    public function containsArabic(string $text): bool
    {
        return (bool) preg_match('/\p{Arabic}/u', $text);
    }

    /**
     * Extract Arabic and English separately, normalize each appropriately.
     *
     * For mixed Arabic/English text (common in Libyan search),
     * normalize both components and return combined.
     *
     * Example:
     *   "أسنان teeth" → "اسنان teeth"
     */
    public function normalizeMixed(string $text): string
    {
        // For now, use simple normalization that handles both
        // Full separation would be more complex
        return $this->normalize($text);
    }
}
