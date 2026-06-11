<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Log;

/**
 * Validate AI responses for safety.
 *
 * Prevents:
 * - HTML injection
 * - JavaScript injection
 * - Markdown link attacks
 * - Prompt leakage
 * - API key exposure
 * - File path leakage
 */
class OutputValidator
{
    private const DANGEROUS_PATTERNS = [
        '/<script[^>]*>.*?<\/script>/is',
        '/<iframe[^>]*>.*?<\/iframe>/is',
        '/<object[^>]*>.*?<\/object>/is',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/var\/www/i',
        '/\/app\//i',
        '/\.env/i',
        '/config\//i',
        '/sk-[a-zA-Z0-9]{20,}/i',
        '/deepseek[_-]?key/i',
        '/api[_-]?key/i',
        '/secret[_-]?key/i',
        '/system[_-]?prompt/i',
        '/hidden[_-]?instruction/i',
        '/secret[_-]?instruction/i',
        '/ignore[_-]?previous/i',
        '/forget[_-]?previous/i',
    ];

    public function validate(mixed $output): array
    {
        if (! is_string($output) && ! is_array($output)) {
            return [
                'valid' => false,
                'reason' => 'Output must be string or array',
                'output' => null,
            ];
        }

        // If string, must be valid JSON
        if (is_string($output)) {
            $decoded = json_decode($output, true);
            if (! is_array($decoded)) {
                return [
                    'valid' => false,
                    'reason' => 'String output must be valid JSON',
                    'output' => null,
                ];
            }
            $output = $decoded;
        }

        $outputStr = is_array($output) ? json_encode($output) : $output;

        // Check length (prevent token dump attacks)
        if (strlen($outputStr) > 10000) {
            Log::channel('chatbot-security')->warning('Output exceeds length limit', [
                'length' => strlen($outputStr),
            ]);

            return [
                'valid' => false,
                'reason' => 'Output too long',
                'output' => null,
            ];
        }

        // Check for dangerous patterns
        foreach (self::DANGEROUS_PATTERNS as $pattern) {
            if (preg_match($pattern, $outputStr)) {
                Log::channel('chatbot-security')->warning('Dangerous pattern detected', [
                    'pattern' => $pattern,
                    'preview' => substr($outputStr, 0, 200),
                ]);

                return [
                    'valid' => false,
                    'reason' => 'Dangerous content detected',
                    'output' => null,
                ];
            }
        }

        // If array, validate required fields
        if (is_array($output)) {
            $required = ['specialty', 'city', 'confidence', 'needs_clarification'];
            foreach ($required as $field) {
                if (! isset($output[$field])) {
                    return [
                        'valid' => false,
                        'reason' => "Missing required field: {$field}",
                        'output' => null,
                    ];
                }
            }

            // Validate confidence is 0-1
            if (! is_float($output['confidence']) && ! is_int($output['confidence'])) {
                return [
                    'valid' => false,
                    'reason' => 'confidence must be a number',
                    'output' => null,
                ];
            }

            $confidence = (float) $output['confidence'];
            if ($confidence < 0 || $confidence > 1) {
                return [
                    'valid' => false,
                    'reason' => 'confidence must be between 0 and 1',
                    'output' => null,
                ];
            }
        }

        return [
            'valid' => true,
            'reason' => null,
            'output' => $output,
        ];
    }

    /**
     * Sanitize string output (remove potentially harmful content).
     */
    public function sanitize(string $text): string
    {
        // Remove HTML tags
        $text = strip_tags($text);

        // Remove potential file paths
        $text = preg_replace('/[\/\\\\][\w\-\.]+[\/\\\\][\w\-\.]+/i', '[path]', $text);

        // Remove API key patterns
        $text = preg_replace('/sk-[a-zA-Z0-9]{20,}/i', '[key]', $text);

        // Remove URLs (except domain)
        $text = preg_replace('/https?:\/\/[^\s]+/i', '[url]', $text);

        return $text;
    }

    /**
     * Check if output contains prompt injection indicators.
     */
    public function containsPromptInjection(string $output): bool
    {
        $injectionIndicators = [
            'system prompt',
            'hidden instruction',
            'secret instruction',
            'forget previous',
            'ignore previous',
            'override',
            'bypass',
        ];

        $lowerOutput = strtolower($output);

        foreach ($injectionIndicators as $indicator) {
            if (strpos($lowerOutput, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if output tries to reveal API keys.
     */
    public function containsApiKey(string $output): bool
    {
        return (bool) preg_match('/sk-[a-zA-Z0-9]{20,}/i', $output) ||
               preg_match('/deepseek[_-]?key/i', $output) ||
               preg_match('/api[_-]?key.*[=:]/i', $output);
    }

    /**
     * Check if output contains file paths.
     */
    public function containsFilePaths(string $output): bool
    {
        return (bool) preg_match('/[\/\\\\](?:var|app|home|opt|usr)\//i', $output) ||
               preg_match('/\.env/i', $output) ||
               preg_match('/C:\\\\[a-z]:\\\\/i', $output);
    }
}
