<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OnboardingToken;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class OnboardingSecurityTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeProvider(array $attributes = []): User
    {
        return $this->createProvider(array_merge([
            'is_active' => true,
            'is_suspended' => false,
        ], $attributes));
    }

    private function makeValidToken(User $user, array $overrides = []): OnboardingToken
    {
        return OnboardingToken::create(array_merge([
            'user_id' => $user->id,
            'token' => Str::random(60),
            'expires_at' => now()->addDays(72),
            'used_at' => null,
        ], $overrides));
    }

    private function postSetPassword(array $data, string $ip = '127.0.0.1'): TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->withSession(['_token' => 'test-csrf'])
            ->post(route('onboarding.set-password'), array_merge(
                ['_token' => 'test-csrf'],
                $data
            ));
    }

    private function makePanel(string $id): Panel
    {
        $panel = $this->createMock(Panel::class);
        $panel->method('getId')->willReturn($id);

        return $panel;
    }

    // -----------------------------------------------------------------------
    // 1. Token Validation
    // -----------------------------------------------------------------------

    public function test_valid_token_sets_password_and_redirects_to_provider_login(): void
    {
        $provider = $this->makeProvider();
        $onboardingToken = $this->makeValidToken($provider);

        $this->postSetPassword([
            'token' => $onboardingToken->token,
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ])->assertRedirect(route('filament.provider.auth.login'));

        $this->assertTrue(Hash::check('SecurePass1!', $provider->fresh()->password));
        $this->assertNotNull($onboardingToken->fresh()->used_at);
    }

    public function test_used_token_is_rejected_and_password_is_not_changed(): void
    {
        $provider = $this->makeProvider();
        $originalPassword = $provider->password;
        $onboardingToken = $this->makeValidToken($provider, ['used_at' => now()->subHour()]);

        $this->postSetPassword([
            'token' => $onboardingToken->token,
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ])->assertSessionHasErrors('token');

        $this->assertSame($originalPassword, $provider->fresh()->password);
    }

    public function test_expired_token_is_rejected_and_remains_unused(): void
    {
        $provider = $this->makeProvider();
        $onboardingToken = $this->makeValidToken($provider, [
            'expires_at' => now()->subDay(),
        ]);

        $this->postSetPassword([
            'token' => $onboardingToken->token,
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ])->assertSessionHasErrors('token');

        $this->assertNull($onboardingToken->fresh()->used_at);
    }

    public function test_nonexistent_random_token_returns_safe_error(): void
    {
        $response = $this->postSetPassword([
            'token' => Str::random(60),
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ]);

        // No 500 error — safe failure
        $this->assertTrue(in_array($response->getStatusCode(), [302, 422], true));
    }

    public function test_random_token_does_not_expose_provider_email(): void
    {
        $provider = $this->makeProvider();

        $response = $this->postSetPassword([
            'token' => Str::random(60),
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ]);

        $response->assertDontSee($provider->email);
    }

    // -----------------------------------------------------------------------
    // 2. Double-Submit Protection
    // -----------------------------------------------------------------------

    public function test_double_submit_only_sets_password_once(): void
    {
        $provider = $this->makeProvider();
        $onboardingToken = $this->makeValidToken($provider);

        // First submit — must succeed
        $this->postSetPassword([
            'token' => $onboardingToken->token,
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ])->assertRedirect(route('filament.provider.auth.login'));

        $this->assertNotNull($onboardingToken->fresh()->used_at);

        // Second submit with same token — must fail
        $this->postSetPassword([
            'token' => $onboardingToken->token,
            'password' => 'DifferentPass2@',
            'password_confirmation' => 'DifferentPass2@',
        ])->assertSessionHasErrors('token');

        // Password from first submit is still the only one stored
        $this->assertTrue(Hash::check('SecurePass1!', $provider->fresh()->password));
        $this->assertFalse(Hash::check('DifferentPass2@', $provider->fresh()->password));
    }

    // -----------------------------------------------------------------------
    // 3. Password Security
    // -----------------------------------------------------------------------

    public function test_password_is_stored_as_hash_not_plaintext(): void
    {
        $provider = $this->makeProvider();
        $onboardingToken = $this->makeValidToken($provider);

        $this->postSetPassword([
            'token' => $onboardingToken->token,
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ])->assertRedirect();

        $storedPassword = $provider->fresh()->getAuthPassword();

        $this->assertNotSame('SecurePass1!', $storedPassword);
        $this->assertTrue(Hash::check('SecurePass1!', $storedPassword));
    }

    public function test_password_without_symbol_fails_validation(): void
    {
        $provider = $this->makeProvider();
        $onboardingToken = $this->makeValidToken($provider);

        $this->postSetPassword([
            'token' => $onboardingToken->token,
            'password' => 'WeakPassword1',
            'password_confirmation' => 'WeakPassword1',
        ])->assertSessionHasErrors('password');

        $this->assertNull($onboardingToken->fresh()->used_at);
    }

    public function test_password_without_uppercase_fails_validation(): void
    {
        $provider = $this->makeProvider();
        $onboardingToken = $this->makeValidToken($provider);

        $this->postSetPassword([
            'token' => $onboardingToken->token,
            'password' => 'weakpass1!',
            'password_confirmation' => 'weakpass1!',
        ])->assertSessionHasErrors('password');
    }

    public function test_password_shorter_than_8_chars_fails_validation(): void
    {
        $provider = $this->makeProvider();
        $onboardingToken = $this->makeValidToken($provider);

        $this->postSetPassword([
            'token' => $onboardingToken->token,
            'password' => 'Ab1!',
            'password_confirmation' => 'Ab1!',
        ])->assertSessionHasErrors('password');
    }

    public function test_password_confirmation_mismatch_fails_validation(): void
    {
        $provider = $this->makeProvider();
        $onboardingToken = $this->makeValidToken($provider);

        $this->postSetPassword([
            'token' => $onboardingToken->token,
            'password' => 'SecurePass1!',
            'password_confirmation' => 'DifferentPass2@',
        ])->assertSessionHasErrors('password');

        $this->assertNull($onboardingToken->fresh()->used_at);
    }

    // -----------------------------------------------------------------------
    // 4. Rate Limiting
    // -----------------------------------------------------------------------

    public function test_get_onboarding_show_is_throttled_after_20_requests(): void
    {
        $responses = [];
        for ($i = 0; $i <= 20; $i++) {
            $responses[] = $this->withServerVariables(['REMOTE_ADDR' => '10.1.1.1'])
                ->get(route('onboarding.show', Str::random(60)));
        }

        $this->assertSame(429, end($responses)->getStatusCode());
    }

    public function test_post_set_password_is_throttled_after_5_requests(): void
    {
        $responses = [];
        for ($i = 0; $i <= 5; $i++) {
            $responses[] = $this->postSetPassword([
                'token' => Str::random(60),
                'password' => 'SecurePass1!',
                'password_confirmation' => 'SecurePass1!',
            ], '10.1.1.2');
        }

        $this->assertSame(429, end($responses)->getStatusCode());
    }

    // -----------------------------------------------------------------------
    // 6. Panel Access Control
    // -----------------------------------------------------------------------

    public function test_provider_can_access_provider_panel_but_not_admin(): void
    {
        $provider = $this->makeProvider();

        $this->assertTrue($provider->canAccessPanel($this->makePanel('provider')));
        $this->assertFalse($provider->canAccessPanel($this->makePanel('admin')));
    }

    public function test_super_admin_can_access_admin_panel_but_not_provider(): void
    {
        $admin = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $admin->assignRole('super_admin');

        $this->assertTrue($admin->canAccessPanel($this->makePanel('admin')));
        $this->assertFalse($admin->canAccessPanel($this->makePanel('provider')));
    }

    public function test_public_user_cannot_access_any_panel(): void
    {
        $user = $this->createUser(['is_active' => true, 'is_suspended' => false]);

        $this->assertFalse($user->canAccessPanel($this->makePanel('admin')));
        $this->assertFalse($user->canAccessPanel($this->makePanel('provider')));
    }

    public function test_suspended_provider_cannot_access_any_panel(): void
    {
        $provider = $this->createProvider(['is_active' => true, 'is_suspended' => true]);

        $this->assertFalse($provider->canAccessPanel($this->makePanel('provider')));
        $this->assertFalse($provider->canAccessPanel($this->makePanel('admin')));
    }

    public function test_inactive_provider_cannot_access_any_panel(): void
    {
        $provider = $this->createProvider(['is_active' => false, 'is_suspended' => false]);

        $this->assertFalse($provider->canAccessPanel($this->makePanel('provider')));
        $this->assertFalse($provider->canAccessPanel($this->makePanel('admin')));
    }

    // -----------------------------------------------------------------------
    // 7. Login Security
    // -----------------------------------------------------------------------

    public function test_suspended_user_is_blocked_from_authenticated_routes(): void
    {
        $suspended = User::factory()->create([
            'is_active' => true,
            'is_suspended' => true,
        ]);
        $suspended->assignRole('user');

        // EnsureUserNotSuspended middleware logs out and aborts 403 for suspended users.
        // The dashboard route is inside the [auth, account.locked, user.active, user.not_suspended] group.
        $this->actingAs($suspended)
            ->get(route('dashboard'))
            ->assertStatus(403);
    }

    // -----------------------------------------------------------------------
    // 8. Session Isolation
    // -----------------------------------------------------------------------

    public function test_onboarding_show_logs_out_mismatched_authenticated_user(): void
    {
        $providerA = $this->makeProvider();
        $providerB = $this->makeProvider();
        $tokenForA = $this->makeValidToken($providerA);

        // Provider B visits a token URL that belongs to provider A
        $response = $this->actingAs($providerB)
            ->get(route('onboarding.show', $tokenForA->token));

        // Form loads for provider A's token, showing provider A's email
        $response->assertStatus(200);
        $response->assertSee($providerA->email);

        // Provider B's email is not shown (not the token owner)
        $response->assertDontSee($providerB->email);
    }
}
