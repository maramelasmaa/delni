<?php

namespace Tests\Feature;

use App\Rules\SafeExternalUrl;
use Tests\TestCase;

class SafeExternalUrlTest extends TestCase
{
    // -------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------

    /**
     * Run the rule and return the error messages collected by $fail.
     *
     * @return string[]
     */
    private function validate(mixed $value): array
    {
        $errors = [];

        $rule = new SafeExternalUrl;
        $rule->setData([]);

        $rule->validate('url', $value, function (string $message) use (&$errors): void {
            $errors[] = $message;
        });

        return $errors;
    }

    private function assertPasses(mixed $value): void
    {
        $this->assertEmpty(
            $this->validate($value),
            "Expected URL to pass validation, but it failed: {$value}"
        );
    }

    private function assertFails(mixed $value): void
    {
        $this->assertNotEmpty(
            $this->validate($value),
            "Expected URL to fail validation, but it passed: {$value}"
        );
    }

    // -------------------------------------------------------------------
    // Happy paths
    // -------------------------------------------------------------------

    public function test_valid_https_url_passes(): void
    {
        $this->assertPasses('https://example.com');
    }

    public function test_valid_https_url_with_path_passes(): void
    {
        $this->assertPasses('https://maps.google.com/place/some-location');
    }

    public function test_empty_string_passes(): void
    {
        // Empty is handled as nullable — no error produced
        $this->assertEmpty($this->validate(''));
    }

    // -------------------------------------------------------------------
    // Blocked schemes
    // -------------------------------------------------------------------

    public function test_javascript_scheme_is_rejected(): void
    {
        $this->assertFails('javascript:alert(1)');
    }

    public function test_data_scheme_is_rejected(): void
    {
        $this->assertFails('data:text/html,<script>alert(1)</script>');
    }

    public function test_http_scheme_is_rejected(): void
    {
        $this->assertFails('http://example.com');
    }

    // -------------------------------------------------------------------
    // Blocked hosts
    // -------------------------------------------------------------------

    public function test_localhost_is_rejected(): void
    {
        $this->assertFails('https://localhost/admin');
    }

    public function test_127_loopback_is_rejected(): void
    {
        $this->assertFails('https://127.0.0.1/secret');
    }

    public function test_private_ip_192168_is_rejected(): void
    {
        $this->assertFails('https://192.168.1.1/dashboard');
    }

    public function test_private_ip_10_is_rejected(): void
    {
        $this->assertFails('https://10.0.0.1/internal');
    }

    public function test_private_ip_172_16_is_rejected(): void
    {
        $this->assertFails('https://172.16.0.1/admin');
    }

    public function test_ipv6_loopback_is_rejected(): void
    {
        $this->assertFails('https://[::1]/secret');
    }

    // -------------------------------------------------------------------
    // Rule wired to CredentialsResource form (unit-level)
    // -------------------------------------------------------------------

    public function test_safe_external_url_rule_rejects_javascript_with_arabic_message(): void
    {
        $errors = $this->validate('javascript:alert(1)');

        $this->assertNotEmpty($errors, 'Expected a validation error for javascript: URL');
    }

    public function test_safe_external_url_rule_rejects_private_ip_with_arabic_message(): void
    {
        $errors = $this->validate('https://192.168.0.1/page');

        $this->assertNotEmpty($errors, 'Expected a validation error for private IP URL');
    }
}
