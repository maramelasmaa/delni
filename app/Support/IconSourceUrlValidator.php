<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class IconSourceUrlValidator
{
    private const HEAD_TIMEOUT_SECONDS = 5;

    private const GET_TIMEOUT_SECONDS = 10;

    private const CONNECT_TIMEOUT_SECONDS = 3;

    /** @var \Closure(string): array<int, string> */
    private \Closure $dnsResolver;

    public function __construct(?\Closure $dnsResolver = null)
    {
        $this->dnsResolver = $dnsResolver ?? $this->defaultDnsResolver(...);
    }

    public function validate(string $url): string
    {
        $normalizedUrl = trim($url);

        if ($normalizedUrl === '') {
            throw new InvalidArgumentException('Icon URL is required.');
        }

        $parsed = parse_url($normalizedUrl);

        if ($parsed === false) {
            throw new InvalidArgumentException('Icon URL format is invalid.');
        }

        $scheme = strtolower((string) ($parsed['scheme'] ?? ''));
        $host = strtolower((string) ($parsed['host'] ?? ''));
        $user = $parsed['user'] ?? null;
        $pass = $parsed['pass'] ?? null;
        $port = $parsed['port'] ?? null;

        if ($scheme !== 'https') {
            throw new InvalidArgumentException('Icon URL must use HTTPS.');
        }

        if ($host === '') {
            throw new InvalidArgumentException('Icon URL host is invalid.');
        }

        if ($user !== null || $pass !== null) {
            throw new InvalidArgumentException('Icon URL credentials are not allowed.');
        }

        if ($port !== null && (int) $port !== 443) {
            throw new InvalidArgumentException('Icon URL must use the default HTTPS port.');
        }

        if ($this->isForbiddenHost($host)) {
            throw new InvalidArgumentException('Icon URL host is not allowed.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            if (! $this->isPublicIp($host)) {
                throw new InvalidArgumentException('Icon URL host must resolve to a public IP address.');
            }

            return $normalizedUrl;
        }

        if (! filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new InvalidArgumentException('Icon URL host is invalid.');
        }

        $resolvedIps = ($this->dnsResolver)($host);

        if ($resolvedIps === []) {
            throw new InvalidArgumentException('Icon URL host could not be resolved.');
        }

        foreach ($resolvedIps as $ip) {
            if (! $this->isPublicIp($ip)) {
                throw new InvalidArgumentException('Icon URL host must resolve only to public IP addresses.');
            }
        }

        return $normalizedUrl;
    }

    public function probe(string $url): void
    {
        $response = $this->newRequest(self::HEAD_TIMEOUT_SECONDS)->head($this->validate($url));

        if ($response->redirect()) {
            throw new InvalidArgumentException('Redirecting icon URLs are not allowed.');
        }

        if (! $response->successful()) {
            throw new InvalidArgumentException('Icon URL could not be reached.');
        }

        $contentType = strtolower((string) $response->header('content-type'));

        if (! Str::startsWith($contentType, ['image/svg+xml', 'image/png'])) {
            throw new InvalidArgumentException('URL must point to an SVG or PNG icon.');
        }
    }

    public function fetch(string $url): Response
    {
        $response = $this->newRequest(self::GET_TIMEOUT_SECONDS)
            ->retry(3, 100, fn (\Throwable $exception) => $exception instanceof ConnectionException)
            ->get($this->validate($url));

        if ($response->redirect()) {
            throw new InvalidArgumentException('Redirecting icon URLs are not allowed.');
        }

        $response->throw();

        return $response;
    }

    private function newRequest(int $timeoutSeconds): PendingRequest
    {
        return Http::accept('image/svg+xml, image/png')
            ->timeout($timeoutSeconds)
            ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->withOptions([
                'allow_redirects' => false,
            ]);
    }

    private function isForbiddenHost(string $host): bool
    {
        return in_array($host, [
            'localhost',
            'localhost.localdomain',
            '0.0.0.0',
            '::1',
        ], true);
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /** @return array<int, string> */
    private function defaultDnsResolver(string $host): array
    {
        $records = dns_get_record($host, DNS_A + DNS_AAAA);

        if ($records === false) {
            return [];
        }

        return collect($records)
            ->map(fn (array $record): ?string => $record['ip'] ?? $record['ipv6'] ?? null)
            ->filter(fn (?string $ip): bool => is_string($ip) && $ip !== '')
            ->values()
            ->all();
    }
}
