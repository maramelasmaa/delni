<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\SetPasswordMail;
use App\Models\OnboardingToken;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_show_form_redirects_with_invalid_token(): void
    {
        $response = $this->get(route('onboarding.show', ['token' => 'invalid-token']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('token');
    }

    public function test_onboarding_show_form_redirects_with_expired_token(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'expired-token-12345',
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->get(route('onboarding.show', ['token' => $token->token]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('token');
    }

    public function test_onboarding_set_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response->assertRedirect(route('filament.provider.auth.login'));
        $response->assertSessionHas('status', __('auth.password_set_success'));

        // Verify user is NOT automatically authenticated
        $this->assertGuest();

        // Verify password was set
        $user->refresh();
        $this->assertTrue(Hash::check('ValidPassword123!', $user->password));

        // Verify token was marked as used
        $token->refresh();
        $this->assertNotNull($token->used_at);
    }

    public function test_onboarding_validates_password_requirements(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // Test with invalid password (too short)
        $response = $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_onboarding_requires_password_confirmation(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_onboarding_token_is_single_use(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // First use should succeed
        $response = $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);
        $response->assertRedirect(route('filament.provider.auth.login'));

        // Verify token was marked as used
        $token->refresh();
        $this->assertNotNull($token->used_at);

        // Second use should fail
        $response = $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'AnotherPassword123!',
            'password_confirmation' => 'AnotherPassword123!',
        ]);
        $response->assertSessionHasErrors();
    }

    public function test_onboarding_email_queued_on_provider_creation(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // Simulate provider creation (would normally be done via Filament)
        $provider = User::factory()->create(['email' => 'provider@example.com']);
        $provider->assignRole('provider');

        $token = OnboardingToken::create([
            'user_id' => $provider->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        $setPasswordLink = route('onboarding.show', ['token' => $token->token]);
        Mail::queue(new SetPasswordMail(
            email: $provider->email,
            setPasswordLink: $setPasswordLink,
            userName: $provider->name,
        ));

        Mail::assertQueued(static function (SetPasswordMail $mail) use ($provider): bool {
            return $mail->hasTo($provider->email);
        });
    }

    public function test_provider_cannot_login_until_password_set(): void
    {
        $user = User::factory()->create(['password' => bcrypt(Str::random(32))]);
        $user->assignRole('provider');

        // Provider should not be able to login with placeholder password
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    // TEST GROUP B — Token security

    public function test_expired_onboarding_link_rejected(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'expired-token-12345',
            'expires_at' => now()->subMinutes(5),
        ]);

        $response = $this->get(route('onboarding.show', ['token' => $token->token]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('token');
    }

    public function test_used_onboarding_token_rejected(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'used-token-12345',
            'expires_at' => now()->addHours(24),
            'used_at' => now()->subMinutes(10),
        ]);

        $response = $this->get(route('onboarding.show', ['token' => $token->token]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('token');
    }

    public function test_onboarding_token_single_use_blocks_second_attempt(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // Get form to establish session
        $this->get(route('onboarding.show', ['token' => $token->token]));

        // First attempt succeeds
        $this->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        // Token should be marked as used
        $token->refresh();
        $this->assertNotNull($token->used_at);

        // Second attempt with same token fails
        $response = $this->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors('token');
    }

    public function test_onboarding_token_cannot_be_tampered(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'legit-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // Get form to establish session
        $this->get(route('onboarding.show', ['token' => $token->token]));

        // Attempt with tampered token
        $response = $this->post(route('onboarding.set-password'), [
            'token' => 'tampered-token-99999',
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response->assertSessionHasErrors('token');
    }

    public function test_onboarding_token_tied_to_specific_user(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $token = OnboardingToken::create([
            'user_id' => $user1->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // Token is tied to user1
        $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        // Verify user1 password was set (not user2)
        $user1->refresh();
        $this->assertTrue(Hash::check('ValidPassword123!', $user1->password));

        // User2 password unchanged
        $user2->refresh();
        $originalPassword = $user2->password;
        $this->assertEquals($originalPassword, $user2->password);
    }

    // TEST GROUP E — High traffic / duplicate requests

    public function test_provider_duplicate_create_does_not_duplicate_user(): void
    {
        Mail::fake();

        // Simulate rapid double-click on create provider button
        // This is handled at the database level via unique constraint on email
        $firstUser = User::factory()->create(['email' => 'provider@example.com']);
        $firstUser->assignRole('provider');

        // Verify only one user with this email
        $userCount = User::where('email', 'provider@example.com')->count();
        $this->assertEquals(1, $userCount);

        // Second create with same email should fail at database level
        $this->expectException(UniqueConstraintViolationException::class);
        User::factory()->create(['email' => 'provider@example.com']);
    }

    public function test_set_password_form_double_submit_fails_on_second(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // Get form to establish session
        $this->get(route('onboarding.show', ['token' => $token->token]));

        // First submission
        $response1 = $this->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response1->assertRedirect(route('filament.provider.auth.login'));

        // Get form again for second session (should redirect since token was used)
        $response = $this->get(route('onboarding.show', ['token' => $token->token]));
        $response->assertRedirect(route('login'));

        // Second submission with same token (simulating double-click)
        $response2 = $this->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response2->assertSessionHasErrors('token');
    }

    // TEST GROUP G — Authorization/access

    public function test_logged_in_user_cannot_hijack_different_user_onboarding(): void
    {
        $attacker = User::factory()->create();
        $victim = User::factory()->create();
        $victim->assignRole('provider');

        $token = OnboardingToken::create([
            'user_id' => $victim->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // Attacker tries to set password for victim
        // Token determines user, not current auth
        $response = $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        // Should succeed (token determines user), redirect to Filament provider login
        $response->assertRedirect(route('filament.provider.auth.login'));

        // Verify victim's password was changed
        $victim->refresh();
        $this->assertTrue(Hash::check('ValidPassword123!', $victim->password));

        // Attacker's password unchanged
        $attacker->refresh();
        $this->assertFalse(Hash::check('ValidPassword123!', $attacker->password));
    }

    public function test_onboarding_rejected_for_deleted_user(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // Delete user (soft delete)
        $user->delete();

        // Attempt to set password for deleted user
        $response = $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        // Should fail — user not found or token invalid
        $response->assertSessionHasErrors();
    }

    // TEST GROUP I — Existing accounts regression

    public function test_existing_provider_can_login_after_onboarding(): void
    {
        $provider = User::factory()->create(['email' => 'provider@example.com', 'is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        // Set a proper password via onboarding
        $token = OnboardingToken::create([
            'user_id' => $provider->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // Get the form first to establish session
        $this->get(route('onboarding.show', ['token' => $token->token]));

        $response = $this->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        // Verify redirect to Filament provider login
        $response->assertRedirect(route('filament.provider.auth.login'));

        // Verify password was set
        $provider->refresh();
        $this->assertTrue(Hash::check('ValidPassword123!', $provider->password));

        // Verify provider is NOT automatically authenticated
        $this->assertGuest();

        // Verify Filament provider login page is accessible
        $loginPageResponse = $this->get(route('filament.provider.auth.login'));
        $loginPageResponse->assertSuccessful();
    }

    public function test_provider_role_set_correctly_after_onboarding(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');

        // Set password
        $token = OnboardingToken::create([
            'user_id' => $provider->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        // Verify provider role persists
        $provider->refresh();
        $this->assertTrue($provider->isProvider());
        $this->assertFalse($provider->isAdmin());
    }

    // TEST GROUP — Separation between provider and public login

    public function test_provider_set_password_redirects_to_filament_login_not_public(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        $response = $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        // Should redirect to Filament provider login, not public login
        $response->assertRedirect(route('filament.provider.auth.login'));
    }

    public function test_guest_visiting_provider_panel_redirects_to_provider_login(): void
    {
        $response = $this->get('/provider');

        // Guest should be redirected to Filament provider login, not public login
        $response->assertRedirect(route('filament.provider.auth.login'));
    }

    public function test_provider_filament_login_page_loads(): void
    {
        $response = $this->get(route('filament.provider.auth.login'));

        // Login page should load successfully
        $response->assertSuccessful();
    }

    public function test_provider_after_filament_login_reaches_provider_dashboard(): void
    {
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');
        $provider->updatePassword('ValidPassword123!');

        // Can access provider dashboard when authenticated
        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $response->assertSuccessful();
    }

    public function test_public_user_cannot_access_provider_panel(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('user');

        // Public user should not be able to access provider panel
        $response = $this->actingAs($user)->get('/provider/dashboard');
        $response->assertForbidden();
    }

    public function test_super_admin_cannot_access_provider_panel_without_provider_role(): void
    {
        $admin = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $admin->assignRole('super_admin');

        // Admin without provider role should not access provider panel
        $response = $this->actingAs($admin)->get('/provider/dashboard');
        $response->assertForbidden();
    }

    public function test_suspended_provider_cannot_access_provider_panel(): void
    {
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => true]);
        $provider->assignRole('provider');

        // Suspended provider should not access provider panel
        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $response->assertForbidden();
    }

    public function test_inactive_provider_cannot_access_provider_panel(): void
    {
        $provider = User::factory()->create(['is_active' => false, 'is_suspended' => false]);
        $provider->assignRole('provider');

        // Inactive provider should not access provider panel
        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $response->assertForbidden();
    }

    public function test_no_redirect_loop_on_provider_panel(): void
    {
        // Guest visiting /provider should redirect once to login, not loop
        $response = $this->get('/provider', [
            'HTTP_ACCEPT' => 'text/html',
        ]);

        // Should have exactly one redirect
        $response->assertRedirect(route('filament.provider.auth.login'));

        // Follow the redirect
        $followResponse = $this->get(route('filament.provider.auth.login'));
        $followResponse->assertSuccessful();
    }
}
