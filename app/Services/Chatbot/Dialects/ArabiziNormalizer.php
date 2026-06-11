<?php

namespace App\Services\Chatbot\Dialects;

/**
 * Convert Arabizi (Arabic written with Latin characters + numbers) to Arabic.
 *
 * Examples:
 * - "3andak" → "عندك"
 * - "nbi doctor" → "نبي دكتور"
 * - "7amid" → "حامد"
 * - "khelaas" → "خلاص"
 */
class ArabiziNormalizer
{
    /**
     * Mapping of Arabizi patterns to Arabic characters.
     */
    private array $arabiziMap = [
        // Numbers (most common)
        '3' => 'ع',
        '7' => 'ح',
        '8' => 'ق',
        '9' => 'ق',
        '0' => 'ع',
        '2' => 'ء',
        '1' => 'ا',
        '5' => 'خ',
        '6' => 'ط',
        '4' => 'ا',

        // Common Latin combinations
        'kh' => 'خ',
        'sh' => 'ش',
        'th' => 'ث',
        'gh' => 'غ',
        'dh' => 'ذ',
        'q' => 'ق',
        'j' => 'ج',
        'z' => 'ز',
        'x' => 'خ',

        // Single letters
        'a' => 'ا',
        'b' => 'ب',
        'c' => 'س',
        'd' => 'د',
        'e' => 'ي',
        'f' => 'ف',
        'g' => 'غ',
        'h' => 'ه',
        'i' => 'ي',
        'k' => 'ك',
        'l' => 'ل',
        'm' => 'م',
        'n' => 'ن',
        'o' => 'و',
        'p' => 'ب',
        'r' => 'ر',
        's' => 'س',
        't' => 'ت',
        'u' => 'و',
        'v' => 'ف',
        'w' => 'و',
        'y' => 'ي',
    ];

    /**
     * Convert Arabizi to Arabic.
     */
    public function toArabic(string $text): string
    {
        $text = strtolower($text);

        // Replace multi-character patterns first (kh, sh, th, etc.)
        $multiChar = ['kh', 'sh', 'th', 'gh', 'dh'];
        foreach ($multiChar as $pattern) {
            if (isset($this->arabiziMap[$pattern])) {
                $text = str_replace($pattern, $this->arabiziMap[$pattern], $text);
            }
        }

        // Replace single characters
        foreach ($this->arabiziMap as $latin => $arabic) {
            if (strlen($latin) === 1) {
                $text = str_replace($latin, $arabic, $text);
            }
        }

        // Clean up remaining Latin that couldn't be converted
        $text = preg_replace('/[a-z]/i', '', $text);

        return $text;
    }
}
