<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Provider\Pages\Auth\Login;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
        ]);
    }

    public function test_provider_login_form_submission(): void
    {
        // Get the login page to initialize session
        $loginPage = $this->get('/provider/login');
        $loginPage->assertOk();

        // The login form in Filament is a Livewire component
        // Try to simulate the form submission
        Livewire::test(Login::class)
            ->set('email', 'provider@livewire.com')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertSuccessful();

        // After successful auth, provider should be logged in
        $this->assertAuthenticatedAs($this->provider);
    }

    public function test_after_login_provider_can_view_dashboard(): void
    {
        $this->actingAs($this->provider);

        $response = $this->get('/provider/dashboard');

        // This should NOT redirect
        $this->assertEquals(200, $response->status());
    }

    public function test_login_form_with_invalid_credentials(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'provider@livewire.com')
            ->set('password', 'wrong-password')
            ->call('authenticate')
            ->assertHasFormErrors('email');
    }
}
