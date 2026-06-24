<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiVersioningTest extends TestCase
{
    public function test_named_routes_resolve_to_v1_endpoints(): void
    {
        $this->assertSame('/api/v1/health', route('api.health', absolute: false));
        $this->assertSame('/api/v1/auth/login', route('api.auth.login', absolute: false));
        $this->assertSame('/api/v1/favorites', route('api.favorites.index', absolute: false));
    }

    public function test_health_endpoint_is_available_under_v1(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'خادم دلني يعمل بنجاح.',
            ]);
    }

    public function test_protected_v1_endpoint_keeps_auth_middleware(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'يرجى تسجيل الدخول أولاً.',
            ]);
    }
}
