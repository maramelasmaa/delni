<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SocialProfileReference implements ValidationRule
{
    /**
     * @param  'instagram'|'facebook'|'linkedin'|'github'  $platform
     */
    public function __construct(
        private readonly string $platform,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (! is_string($value)) {
            $fail('The social profile reference is invalid.');

            return;
        }

        $value = trim($value);

        if ($value === '') {
            return;
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            $this->validateUrlInput($value, $fail);

            return;
        }

        if (! $this->isValidIdentifier($value)) {
            $fail('The social profile reference must be a valid username, slug, or profile URL.');
        }
    }

    private function validateUrlInput(string $value, Closure $fail): void
    {
        $parsed = parse_url($value);

        if ($parsed === false) {
            $fail('The social profile reference must be a valid username, slug, or profile URL.');

            return;
        }

        $scheme = strtolower((string) ($parsed['scheme'] ?? ''));
        $host = strtolower((string) ($parsed['host'] ?? ''));
        $path = trim((string) ($parsed['path'] ?? ''), '/');

        if ($scheme !== 'https' || ! $this->hostMatches($host, $this->expectedHost()) || $path === '') {
            $fail('يجب أن يشير رابط الملف الاجتماعي إلى المنصة المتوقعة.');

            return;
        }

        if (! $this->isValidIdentifier($path)) {
            $fail('The social profile reference must be a valid username, slug, or profile URL.');
        }
    }

    private function isValidIdentifier(string $value): bool
    {
        return match ($this->platform) {
            'instagram' => preg_match('/^@?[A-Za-z0-9._]{1,30}$/', $value) === 1,
            'facebook' => preg_match('/^[A-Za-z0-9._\-\/]{1,255}$/', trim($value, '/')) === 1,
            'linkedin' => preg_match('/^[A-Za-z0-9._\-\/]{1,255}$/', trim($value, '/')) === 1,
            'github' => preg_match('/^@?[A-Za-z0-9](?:[A-Za-z0-9\-]{0,38})$/', $value) === 1
                && ! str_ends_with($value, '-'),
            default => false,
        };
    }

    private function expectedHost(): string
    {
        return match ($this->platform) {
            'instagram' => 'instagram.com',
            'facebook' => 'facebook.com',
            'linkedin' => 'linkedin.com',
            'github' => 'github.com',
            default => '',
        };
    }

    private function hostMatches(string $host, string $expectedHost): bool
    {
        return $host === $expectedHost || str_ends_with($host, '.'.$expectedHost);
    }
}
