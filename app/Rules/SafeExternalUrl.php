<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class SafeExternalUrl implements DataAwareRule, ValidationRule
{
    private array $data = [];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('تعذر التحقق من صحة الرابط.');

            return;
        }

        $url = trim($value);

        if (empty($url)) {
            return;
        }

        // Add scheme if missing
        if (! Str::startsWith($url, ['http://', 'https://'])) {
            $url = 'https://'.$url;
        }

        // Parse URL
        $parsed = parse_url($url);
        if ($parsed === false) {
            $fail('صيغة الرابط غير صحيحة.');

            return;
        }

        $scheme = $parsed['scheme'] ?? '';
        $host = $parsed['host'] ?? '';

        // Check protocol is HTTPS only
        if ($scheme !== 'https') {
            $fail('يجب أن يبدأ الرابط بـ https://');

            return;
        }

        // Block javascript, data, file, ftp, vbscript
        if (in_array($scheme, ['javascript', 'data', 'file', 'ftp', 'vbscript'])) {
            $fail('نوع الرابط غير مسموح.');

            return;
        }

        // Block localhost and private networks
        if ($this->isPrivateNetwork($host)) {
            $fail('الرابط غير مسموح لأسباب أمنية.');

            return;
        }

        // Check if it's an IP address
        $isIP = filter_var($host, FILTER_VALIDATE_IP);

        if ($isIP) {
            // Block private IPs only (allow public IPs)
            if ($this->isPrivateNetwork($host)) {
                $fail('الروابط عبر عناوين IP المحلية غير مسموحة.');

                return;
            }
        } else {
            // Validate domain format (only for non-IP hosts)
            if (! $this->isValidDomain($host)) {
                $fail('صيغة المجال غير صحيحة.');

                return;
            }
        }
    }

    private function isPrivateNetwork(string $host): bool
    {
        $host = strtolower($host);

        // localhost
        if ($host === 'localhost' || $host === 'localhost.localdomain') {
            return true;
        }

        // 127.x.x.x
        if (Str::startsWith($host, '127.')) {
            return true;
        }

        // 0.0.0.0
        if ($host === '0.0.0.0') {
            return true;
        }

        // ::1 (IPv6 loopback)
        if ($host === '::1') {
            return true;
        }

        // 10.x.x.x
        if (Str::startsWith($host, '10.')) {
            return true;
        }

        // 172.16.x.x - 172.31.x.x
        if (Str::startsWith($host, '172.')) {
            $parts = explode('.', $host);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $second = (int) $parts[1];
                if ($second >= 16 && $second <= 31) {
                    return true;
                }
            }
        }

        // 192.168.x.x
        if (Str::startsWith($host, '192.168.')) {
            return true;
        }

        return false;
    }

    private function isValidDomain(string $host): bool
    {
        // Remove port if present
        if (strpos($host, ':') !== false) {
            $host = substr($host, 0, strpos($host, ':'));
        }

        // Valid domain pattern: alphanumeric, hyphens, dots
        return (bool) preg_match('/^([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$/i', $host);
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
