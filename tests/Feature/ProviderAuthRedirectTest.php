<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderAuthRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_provider_redirects_once_to_login(): void
    {
        $response = $this->get('/provider');
        $response->assertRedirect('/provider/login');
    }

    public function test_provider_login_page_loads_200(): void
    {
        $response = $this->get('/provider/login');
        $response->assertOk();
    }

    public function test_provider_with_role_can_access_panel(): void
    {
        $provider = User::factory(['is_active' => true, 'is_suspended' => false])->create();
        $provider->assignRole('provider');
        $this->actingAs($provider);

        $response = $this->get('/provider/dashboard');
        $response->assertOk();
    }

    public function test_onboarding_with_invalid_token_redirects(): void
    {
        $response = $this->get(route('onboarding.show', 'invalid-token'));
        $response->assertRedirect(route('login'));
    }

    public function test_public_login_does_not_redirect_provider(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
    }

    public function test_public_user_cannot_access_provider_panel(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/provider');
        $response->assertStatus(403);
    }

    public function test_admin_without_provider_role_cannot_access_panel(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $response = $this->get('/provider');
        $response->assertStatus(403);
    }

    public function test_suspended_provider_cannot_access_panel(): void
    {
        $provider = User::factory(['is_suspended' => true])->create();
        $provider->assignRole('provider');
        $this->actingAs($provider);

        $response = $this->get('/provider');
        $response->assertStatus(403);
    }

    public function test_inactive_provider_cannot_access_panel(): void
    {
        $provider = User::factory(['is_active' => false])->create();
        $provider->assignRole('provider');
        $this->actingAs($provider);

        $response = $this->get('/provider');
        $response->assertStatus(403);
    }

    public function test_no_redirect_loop_on_provider_login(): void
    {
        $response1 = $this->get('/provider/login');
        $response1->assertOk();

        $response2 = $this->get('/provider/login');
        $response2->assertOk();
    }
}
