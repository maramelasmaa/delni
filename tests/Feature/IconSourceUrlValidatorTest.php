<?php

namespace Tests\Feature;

use App\Support\IconSourceUrlValidator;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;

class IconSourceUrlValidatorTest extends TestCase
{
    public function test_rejects_private_ip_hosts(): void
    {
        $validator = new IconSourceUrlValidator;

        $this->expectException(InvalidArgumentException::class);
        $validator->validate('https://127.0.0.1/icon.svg');
    }

    public function test_rejects_urls_with_credentials(): void
    {
        $validator = new IconSourceUrlValidator;

        $this->expectException(InvalidArgumentException::class);
        $validator->validate('https://user:[email protected]/icon.svg');
    }

    public function test_rejects_domains_resolving_to_private_ips(): void
    {
        $validator = new IconSourceUrlValidator(
            fn (string $host): array => $host === 'example.com' ? ['10.0.0.5'] : []
        );

        $this->expectException(InvalidArgumentException::class);
        $validator->validate('https://example.com/icon.svg');
    }

    public function test_probe_rejects_redirecting_urls(): void
    {
        Http::fake([
            'https://cdn.example.com/*' => Http::response('', 302, ['Location' => 'https://127.0.0.1/secret']),
        ]);

        $validator = new IconSourceUrlValidator(
            fn (string $host): array => $host === 'cdn.example.com' ? ['93.184.216.34'] : []
        );

        $this->expectException(InvalidArgumentException::class);
        $validator->probe('https://cdn.example.com/icon.svg');
    }

    public function test_probe_accepts_public_svg_icon_urls(): void
    {
        Http::fake([
            'https://cdn.example.com/*' => Http::response('', 200, ['Content-Type' => 'image/svg+xml']),
        ]);

        $validator = new IconSourceUrlValidator(
            fn (string $host): array => $host === 'cdn.example.com' ? ['93.184.216.34'] : []
        );

        $validator->probe('https://cdn.example.com/icon.svg');

        $this->assertTrue(true);
    }
}
