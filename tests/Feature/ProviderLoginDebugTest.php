<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderLoginDebugTest extends TestCase
{
    use RefreshDatabase;

    protected User $provider;

    protected function setUp(): void
    {
        parent::setUp();

        // Create provider with all required attributes
        $this->provider = User::create([
            'name' => 'Test Provider',
            'email' => 'provider@debug.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $this->provider->assignRole('provider');

        // Create profile
        Profile::create([
            'user_id' => $this->provider->id,
            'slug' => 'test-provider-debug',
        ]);
    }

    public function test_provider_login_get_returns_200(): void
    {
        $response = $this->get('/provider/login');
        $response->assertOk();
    }

    public function test_provider_can_access_panel(): void
    {
        $this->assertTrue(
            $this->provider->canAccessPanel(Filament::getPanel('provider'))
        );
    }

    public function test_provider_is_active(): void
    {
        $this->assertTrue($this->provider->is_active);
    }

    public function test_provider_not_suspended(): void
    {
        $this->assertFalse($this->provider->is_suspended);
    }

    public function test_provider_has_role(): void
    {
        $this->assertTrue($this->provider->hasRole('provider'));
    }

    public function test_authenticated_provider_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->provider)->get('/provider/dashboard');

        // Debug: capture response
        if ($response->status() >= 300 && $response->status() < 400) {
            echo 'REDIRECT: '.$response->getTargetUrl().PHP_EOL;
        }

        $response->assertOk();
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/provider/dashboard');
        $response->assertRedirect('/provider/login');
    }
}
