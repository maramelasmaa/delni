<?php

namespace App\Services\Chatbot\Dialects;

/**
 * Correct common spelling mistakes and voice-to-text errors.
 *
 * Examples:
 * - "dktor" → "doctor"
 * - "dentist" → "dentist" (correct, no change)
 * - "fsiotherapist" → "physiotherapist"
 */
class SpellingCorrector
{
    /**
     * Common misspelling corrections.
     *
     * @var array<string, string>
     */
    private array $corrections = [
        // Medical terms
        'dctor' => 'doctor',
        'dktor' => 'doctor',
        'doctor\'s' => 'doctor',
        'dentist\'s' => 'dentist',
        'fsiotherapist' => 'physiotherapist',
        'therapist\'s' => 'therapist',
        'pychologist' => 'psychologist',
        'psycologist' => 'psychologist',
        'cardiologist\'s' => 'cardiologist',

        // Arabic voice-to-text errors
        'دكتول' => 'دكتور',
        'دكتير' => 'دكتور',
        'دكتر' => 'دكتور',
        'دوكتور' => 'دكتور',

        // Common Libyan dialect variations
        'نبي' => 'ابي',
        'بدي' => 'ابي',
        'ايدي' => 'ابي',
    ];

    /**
     * Correct spelling in text.
     */
    public function correct(string $text): string
    {
        $text = strtolower($text);

        return str_replace(
            array_keys($this->corrections),
            array_values($this->corrections),
            $text,
        );
    }
}
