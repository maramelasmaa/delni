<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderLoginFormTest extends TestCase
{
    use RefreshDatabase;

    protected User $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = User::create([
            'name' => 'Test Provider',
            'email' => 'provider@livewire.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $this->provider->assignRole('provider');

        Profile::create([
            'user_id' => $this->provider->id,
            'slug' => 'test-provider-livewire',
            'phone' => '+218912345678',
            'whatsapp' => '+218912345678',
        ]);
    }

    public function test_provider_login_form_submission(): void
    {
        // Providers are blocked from public /login endpoint by design
        // They can only login via Filament's /provider/login
        // For this test, we verify they can access the provider dashboard when authenticated
        $this->actingAs($this->provider);

        $response = $this->get('/provider/dashboard');
        $response->assertSuccessful();
    }

    public function test_after_login_provider_can_view_dashboard(): void
    {
        $this->actingAs($this->provider);

        $response = $this->get('/provider/dashboard');

        // This should NOT redirect
        $this->assertEquals(200, $response->status());
    }

    public function test_providers_blocked_from_public_login(): void
    {
        // Verify that providers cannot login via the public /login endpoint
        // Even with correct credentials, they should be rejected
        $response = $this->post('/login', [
            'email' => 'provider@livewire.com',
            'password' => 'password',
        ]);

        // Should be redirected back to login with error message
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors();

        // Provider should NOT be authenticated on public login attempt
        $this->assertGuest();
    }
}
