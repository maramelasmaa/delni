<?php

namespace App\Services\Chatbot\Dialects;

/**
 * Normalize Arabic text:
 * - Remove diacritics (fatha, kasra, damma, etc.)
 * - Normalize hamza variations (ا vs إ vs أ vs آ → ا)
 * - Normalize taa variations (ة → ه)
 * - Normalize aleph variations
 *
 * Converts all Arabic to single canonical form.
 */
class ArabicNormalizer
{
    /**
     * Normalize Arabic text to canonical form.
     */
    public function normalize(string $text): string
    {
        // Remove diacritical marks
        $text = $this->removeDiacritics($text);

        // Normalize hamza variations
        $text = $this->normalizeHamza($text);

        // Normalize taa marbuta
        $text = $this->normalizeTaaMarbuta($text);

        // Normalize aleph variations
        $text = $this->normalizeAleph($text);

        return $text;
    }

    /**
     * Remove Arabic diacritics.
     */
    private function removeDiacritics(string $text): string
    {
        $diacritics = [
            'ً', // Fathatan
            'ٌ', // Dammatan
            'ٍ', // Kasratan
            'َ', // Fatha
            'ُ', // Damma
            'ِ', // Kasra
            'ّ', // Shadda
            'ْ', // Sukun
            'ـ', // Tatweel
        ];

        return str_replace($diacritics, '', $text);
    }

    /**
     * Normalize hamza variations: أ إ آ ء → ا
     */
    private function normalizeHamza(string $text): string
    {
        $replacements = [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ء' => '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Normalize taa marbuta: ة → ه
     */
    private function normalizeTaaMarbuta(string $text): string
    {
        return str_replace('ة', 'ه', $text);
    }

    /**
     * Normalize aleph variations
     */
    private function normalizeAleph(string $text): string
    {
        // ى → ي (aleph maksura to ya)
        return str_replace('ى', 'ي', $text);
    }
}
