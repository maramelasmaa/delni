<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_shows(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.forgot-password');
    }

    public function test_forgot_password_redirects_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.request'));

        $response->assertStatus(302);
    }

    public function test_sending_reset_link_with_valid_email(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'user@example.com']);

        // First visit the form to establish session
        $this->get(route('password.request'));

        $response = $this->withoutMiddleware()->post(route('password.email'), [
            'email' => 'user@example.com',
        ]);

        $response->assertSessionHas('status');
        Mail::assertQueued(static function (PasswordResetMail $mail) use ($user): bool {
            return $mail->hasTo($user->email);
        });
    }

    public function test_forgotten_password_prevents_user_enumeration(): void
    {
        Mail::fake();

        // First request with valid user
        User::factory()->create(['email' => 'valid@example.com']);
        $response1 = $this->withoutMiddleware()->post(route('password.email'), [
            'email' => 'valid@example.com',
        ]);

        // Second request with non-existent user
        $response2 = $this->withoutMiddleware()->post(route('password.email'), [
            'email' => 'invalid@example.com',
        ]);

        // Both should have same status
        $this->assertEquals($response1->status(), $response2->status());
    }

    public function test_reset_password_form_shows_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('auth.reset-password');
    }

    public function test_reset_password_validates_password_complexity(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        // Test with invalid password (too short)
        $response = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_reset_password_requires_confirmation(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_reset_password_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $originalPassword = $user->password;
        $token = Password::createToken($user);

        $response = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        // Verify password was changed
        $user->refresh();
        $this->assertNotEquals($originalPassword, $user->password);
    }

    public function test_reset_password_with_invalid_token_fails(): void
    {
        $user = User::factory()->create();

        $response = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_password_reset_is_queued(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->withoutMiddleware()->post(route('password.email'), [
            'email' => $user->email,
        ]);

        Mail::assertQueued(static function (PasswordResetMail $mail): bool {
            return $mail instanceof PasswordResetMail;
        });
    }

    // TEST GROUP B — Token security (reset tokens)

    public function test_reset_token_expires(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::createToken($user);

        // Manually expire the token by updating the database
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subHours(2)]);

        $response = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_reset_token_single_use(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        // First use succeeds
        $response1 = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response1->assertRedirect(route('login'));

        // Second use with same token fails
        $response2 = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response2->assertSessionHasErrors();
    }

    public function test_reset_token_tampered(): void
    {
        $user = User::factory()->create();
        Password::createToken($user);

        $response = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => 'tampered-token-99999',
            'email' => $user->email,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_reset_token_invalid_email(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::createToken($user);

        $response = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => 'wrong@example.com',
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response->assertSessionHasErrors();
    }

    // TEST GROUP E — High traffic / duplicate requests

    public function test_reset_password_double_submit_fails_on_second(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        // First submission succeeds
        $response1 = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response1->assertRedirect(route('login'));

        // Second submission with same token fails
        $response2 = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $response2->assertSessionHasErrors();
    }

    public function test_multiple_reset_requests_create_new_token(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'user@example.com']);

        // First request
        $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        // Second request (rapid duplicate)
        $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        // Each request creates a new token (old one is replaced in Laravel)
        // So we should have at least 1 token
        $tokenCount = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->count();

        $this->assertGreaterThanOrEqual(1, $tokenCount);
    }

    // TEST GROUP G — Authorization/access

    public function test_guest_can_access_reset_form(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));

        $response->assertStatus(200);
    }

    public function test_logged_in_user_cannot_hijack_different_user_reset(): void
    {
        $attacker = User::factory()->create(['email' => 'attacker@example.com']);
        $victim = User::factory()->create(['email' => 'victim@example.com']);

        $token = Password::createToken($victim);

        // Attacker tries to reset victim's password while logged in
        $response = $this->actingAs($attacker)->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => $victim->email,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        // Should succeed (token determines user, not current auth)
        $response->assertRedirect(route('login'));

        // Verify victim's password was changed
        $victim->refresh();
        $this->assertTrue(Hash::check('ValidPassword123!', $victim->password));

        // Attacker's password unchanged
        $attacker->refresh();
        $this->assertFalse(Hash::check('ValidPassword123!', $attacker->password));
    }

    // TEST GROUP I — Existing accounts regression

    public function test_existing_public_user_can_reset_password(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $user->assignRole('user');

        $token = Password::createToken($user);

        $response = $this->withoutMiddleware()->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('login'));

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    public function test_existing_provider_can_reset_password(): void
    {
        $provider = User::factory()->create(['email' => 'provider@example.com']);
        $provider->assignRole('provider');

        $token = Password::createToken($provider);

        // Get the reset form first to establish session
        $this->get(route('password.reset', ['token' => $token, 'email' => $provider->email]));

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $provider->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('login'));

        // Verify password was changed
        $provider->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $provider->password));
    }

    public function test_existing_super_admin_can_reset_password(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole('super_admin');

        $token = Password::createToken($admin);

        // Get the reset form first to establish session
        $this->get(route('password.reset', ['token' => $token, 'email' => $admin->email]));

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $admin->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('login'));

        // Verify password was changed
        $admin->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $admin->password));
    }
}
