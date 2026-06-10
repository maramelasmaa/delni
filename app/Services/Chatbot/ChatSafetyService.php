<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Log;

/**
 * Safety checks for chatbot messages.
 *
 * Protects against:
 * - Prompt injection attempts
 * - SQL injection patterns
 * - Extremely long messages (DoS)
 * - Suspicious command-like patterns
 */
class ChatSafetyService
{
    private const MAX_MESSAGE_LENGTH = 500;

    private array $suspiciousPatterns = [
        // Prompt injection attempts
        'ignore previous',
        'forget previous',
        'disregard',
        'override',
        'bypass',
        'show hidden',
        'reveal hidden',
        'admin access',
        'show suspended',
        'show inactive',
        'database query',
        'select from',
        'exec sql',

        // Command injection
        'rm -rf',
        'drop table',
        'delete from',
        'update where',
        'exec(',
        'system(',
        'eval(',

        // Common injection SQL
        'union select',
        'or 1=1',
        "'; drop",
        "' or '",
    ];

    /**
     * Validate message for safety.
     *
     * Returns array with:
     * - safe: bool
     * - reason: string (if unsafe)
     *
     * @return array<string, mixed>
     */
    public function validate(string $message): array
    {
        // Check length
        if (mb_strlen($message, 'UTF-8') > self::MAX_MESSAGE_LENGTH) {
            return [
                'safe' => false,
                'reason' => 'message_too_long',
            ];
        }

        // Check for suspicious patterns
        if ($this->hasSuspiciousPatterns($message)) {
            Log::warning('Suspicious chatbot message detected', [
                'message_preview' => mb_substr($message, 0, 50),
                'pattern_matched' => true,
            ]);

            return [
                'safe' => false,
                'reason' => 'suspicious_pattern',
            ];
        }

        return [
            'safe' => true,
            'reason' => null,
        ];
    }

    /**
     * Check if message contains suspicious patterns.
     */
    private function hasSuspiciousPatterns(string $message): bool
    {
        $lowerMessage = mb_strtolower($message, 'UTF-8');

        foreach ($this->suspiciousPatterns as $pattern) {
            if (str_contains($lowerMessage, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
