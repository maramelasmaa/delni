<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class SafeExternalUrl implements DataAwareRule, ValidationRule
{
    private array $data = [];

    /**
     * @param  array<int, string>|null  $allowedHosts
     */
    public function __construct(
        private readonly ?array $allowedHosts = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (! is_string($value)) {
            $fail('ØªØ¹Ø°Ø± Ø§Ù„ØªØ­Ù‚Ù‚ Ù…Ù† ØµØ­Ø© Ø§Ù„Ø±Ø§Ø¨Ø·.');

            return;
        }

        $url = trim($value);

        if ($url === '') {
            return;
        }

        if (! Str::startsWith($url, ['http://', 'https://'])) {
            $url = 'https://'.$url;
        }

        $parsed = parse_url($url);

        if ($parsed === false) {
            $fail('ØµÙŠØºØ© Ø§Ù„Ø±Ø§Ø¨Ø· ØºÙŠØ± ØµØ­ÙŠØ­Ø©.');

            return;
        }

        $scheme = $parsed['scheme'] ?? '';
        $host = $parsed['host'] ?? '';
        $user = $parsed['user'] ?? null;
        $pass = $parsed['pass'] ?? null;

        if ($scheme !== 'https') {
            $fail('ÙŠØ¬Ø¨ Ø£Ù† ÙŠØ¨Ø¯Ø£ Ø§Ù„Ø±Ø§Ø¨Ø· Ø¨Ù€ https://');

            return;
        }

        if (in_array($scheme, ['javascript', 'data', 'file', 'ftp', 'vbscript'], true)) {
            $fail('Ù†ÙˆØ¹ Ø§Ù„Ø±Ø§Ø¨Ø· ØºÙŠØ± Ù…Ø³Ù…ÙˆØ­.');

            return;
        }

        if ($user !== null || $pass !== null) {
            $fail('Ø§Ù„Ø±Ø§Ø¨Ø· ØºÙŠØ± Ù…Ø³Ù…ÙˆØ­ Ù„Ø£Ø³Ø¨Ø§Ø¨ Ø£Ù…Ù†ÙŠØ©.');

            return;
        }

        if ($this->isPrivateNetwork($host)) {
            $fail('Ø§Ù„Ø±Ø§Ø¨Ø· ØºÙŠØ± Ù…Ø³Ù…ÙˆØ­ Ù„Ø£Ø³Ø¨Ø§Ø¨ Ø£Ù…Ù†ÙŠØ©.');

            return;
        }

        $isIP = filter_var($host, FILTER_VALIDATE_IP);

        if ($isIP) {
            if ($this->isPrivateNetwork($host)) {
                $fail('Ø§Ù„Ø±ÙˆØ§Ø¨Ø· Ø¹Ø¨Ø± Ø¹Ù†Ø§ÙˆÙŠÙ† IP Ø§Ù„Ù…Ø­Ù„ÙŠØ© ØºÙŠØ± Ù…Ø³Ù…ÙˆØ­Ø©.');

                return;
            }

            if ($this->allowedHosts !== null && $this->allowedHosts !== []) {
                $fail('Ø§Ù„Ø±Ø§Ø¨Ø· ÙŠØ¬Ø¨ Ø£Ù† ÙŠÙƒÙˆÙ† Ù…Ù† Ù…ØµØ¯Ø± Ù…Ø¹ØªÙ…Ø¯.');

                return;
            }

            return;
        }

        if (! $this->isValidDomain($host)) {
            $fail('ØµÙŠØºØ© Ø§Ù„Ù…Ø¬Ø§Ù„ ØºÙŠØ± ØµØ­ÙŠØ­Ø©.');

            return;
        }

        if (! $this->isAllowedHost($host)) {
            $fail('Ø§Ù„Ø±Ø§Ø¨Ø· ÙŠØ¬Ø¨ Ø£Ù† ÙŠÙƒÙˆÙ† Ù…Ù† Ù…ØµØ¯Ø± Ù…Ø¹ØªÙ…Ø¯.');
        }
    }

    private function isPrivateNetwork(string $host): bool
    {
        $host = strtolower($host);

        if ($host === 'localhost' || $host === 'localhost.localdomain') {
            return true;
        }

        if (Str::startsWith($host, '127.')) {
            return true;
        }

        if ($host === '0.0.0.0') {
            return true;
        }

        if ($host === '::1') {
            return true;
        }

        if (Str::startsWith($host, '10.')) {
            return true;
        }

        if (Str::startsWith($host, '172.')) {
            $parts = explode('.', $host);

            if (isset($parts[1]) && is_numeric($parts[1])) {
                $second = (int) $parts[1];

                if ($second >= 16 && $second <= 31) {
                    return true;
                }
            }
        }

        if (Str::startsWith($host, '192.168.')) {
            return true;
        }

        return false;
    }

    private function isValidDomain(string $host): bool
    {
        if (strpos($host, ':') !== false) {
            $host = substr($host, 0, strpos($host, ':'));
        }

        return (bool) preg_match('/^([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$/i', $host);
    }

    private function isAllowedHost(string $host): bool
    {
        if ($this->allowedHosts === null || $this->allowedHosts === []) {
            return true;
        }

        $normalizedHost = strtolower($host);

        foreach ($this->allowedHosts as $allowedHost) {
            $allowedHost = strtolower($allowedHost);

            if ($normalizedHost === $allowedHost || str_ends_with($normalizedHost, '.'.$allowedHost)) {
                return true;
            }
        }

        return false;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
