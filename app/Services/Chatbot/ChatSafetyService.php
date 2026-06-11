<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use Illuminate\Support\Str;

class ChatSafetyService
{
    /**
     * @return array{safe: bool, reason: string|null}
     */
    public function validate(string $message): array
    {
        $lower = Str::lower($message);
        $blocked = [
            'api key',
            'deepseek_api_key',
            'show hidden',
            'hidden providers',
            'ignore rules',
            'system prompt',
            'stack trace',
        ];

        foreach ($blocked as $needle) {
            if (str_contains($lower, $needle)) {
                return ['safe' => false, 'reason' => $needle];
            }
        }

        return ['safe' => true, 'reason' => null];
    }
}
