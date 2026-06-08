<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\OnboardingToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTokenHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        City::factory()->create(['id' => 1]);
        Category::factory()->create(['id' => 1]);
    }

    public function test_onboarding_form_loads_with_valid_token(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $token = 'test_token_12345678901234567890123456789012345678901234567890';
        OnboardingToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(72),
        ]);

        $response = $this->get('/onboarding/'.$token);
        $response->assertSuccessful();
    }

    public function test_set_password_with_valid_token(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $token = 'test_token_12345678901234567890123456789012345678901234567890';
        OnboardingToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(72),
        ]);

        // Submit password form
        $response = $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token,
            'password' => 'TestPassword123!@',
            'password_confirmation' => 'TestPassword123!@',
        ]);

        $response->assertRedirect(route('filament.provider.auth.login'));
        $this->assertGuest();
    }

    public function test_onboarding_token_is_created_for_new_provider(): void
    {
        $admin = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $admin->assignRole('super_admin');

        $provider = User::factory()->create([
            'name' => 'Test Provider',
            'email' => 'provider@example.com',
            'is_active' => true,
            'is_suspended' => false,
        ]);
        $provider->assignRole('provider');

        // Create onboarding token for the provider
        $token = OnboardingToken::create([
            'user_id' => $provider->id,
            'token' => 'test_token_1234567890123456789012345678901234567890123456789',
            'expires_at' => now()->addHours(24),
        ]);

        $this->assertTrue(OnboardingToken::where('user_id', $provider->id)->exists());
        $this->assertTrue($token->isValid());
    }

    public function test_expired_onboarding_token_rejected(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $token = 'expired_token_123456789012345678901234567890123456789012345';
        OnboardingToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->subHours(1), // Expired
        ]);

        $response = $this->get('/onboarding/'.$token);
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('token');
    }

    public function test_already_used_onboarding_token_rejected(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $token = 'used_token_1234567890123456789012345678901234567890123456789';
        OnboardingToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(24),
            'used_at' => now()->subHours(1), // Already used
        ]);

        $response = $this->get('/onboarding/'.$token);
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('token');
    }
}
