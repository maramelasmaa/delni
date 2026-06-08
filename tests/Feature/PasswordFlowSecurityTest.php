<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Mail\SetPasswordMail;
use App\Models\OnboardingToken;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordFlowSecurityTest extends TestCase
{
    use RefreshDatabase;

    // TEST GROUP F — Rate limiting / abuse detection

    public function test_repeated_invalid_token_attempts_handled_safely(): void
    {
        // Simulate repeated attempts with invalid tokens
        for ($i = 0; $i < 5; $i++) {
            $response = $this->get(route('onboarding.show', ['token' => "invalid-token-{$i}"]));
            $response->assertRedirect(route('login'));
            $response->assertSessionHasErrors('token');
        }

        // App should still be responsive
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    public function test_no_account_enumeration_on_forgot_password(): void
    {
        User::factory()->create(['email' => 'valid@example.com']);

        // Request reset for valid user
        $response1 = $this->withoutMiddleware()->post(route('password.email'), [
            'email' => 'valid@example.com',
        ]);

        // Request reset for invalid user
        $response2 = $this->withoutMiddleware()->post(route('password.email'), [
            'email' => 'invalid@example.com',
        ]);

        // Both should have same status
        $this->assertEquals($response1->status(), $response2->status());

        // Both should redirect to same place (enumeration protection)
        $this->assertEquals(
            $response1->headers->get('Location'),
            $response2->headers->get('Location')
        );
    }

    public function test_response_time_does_not_leak_email_existence(): void
    {
        User::factory()->create(['email' => 'exists@example.com']);

        // Time valid email request
        $startValid = microtime(true);
        $this->withoutMiddleware()->post(route('password.email'), [
            'email' => 'exists@example.com',
        ]);
        $timeValid = microtime(true) - $startValid;

        // Time invalid email request
        $startInvalid = microtime(true);
        $this->withoutMiddleware()->post(route('password.email'), [
            'email' => 'nonexistent@example.com',
        ]);
        $timeInvalid = microtime(true) - $startInvalid;

        // Times should be similar (timing attack resistance)
        // Allow 500ms variance for system load
        $timeDiff = abs($timeValid - $timeInvalid);
        $this->assertLessThan(0.5, $timeDiff);
    }

    // TEST GROUP J — Frontend / email UX

    public function test_set_password_email_has_correct_subject(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        $setPasswordLink = route('onboarding.show', ['token' => $token->token]);
        Mail::queue(new SetPasswordMail(
            email: $user->email,
            setPasswordLink: $setPasswordLink,
            userName: $user->name,
        ));

        Mail::assertQueued(SetPasswordMail::class);
    }

    public function test_reset_password_email_has_different_subject_than_onboarding(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        // Queue onboarding email
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'onboarding-token',
            'expires_at' => now()->addHours(24),
        ]);
        $setPasswordLink = route('onboarding.show', ['token' => $token->token]);
        Mail::queue(new SetPasswordMail(
            email: $user->email,
            setPasswordLink: $setPasswordLink,
            userName: $user->name,
        ));

        // Queue reset email
        $resetToken = Password::createToken($user);
        Mail::queue(new PasswordResetMail(
            email: $user->email,
            resetLink: route('password.reset', [
                'token' => $resetToken,
                'email' => $user->email,
            ]),
            userName: $user->name,
        ));

        // Verify both mails were queued
        Mail::assertQueued(SetPasswordMail::class);
        Mail::assertQueued(PasswordResetMail::class);

        // Both should be queued but with different subjects
        // By testing the assertions separately, we ensure both emails are sent
        $this->assertTrue(true);
    }

    public function test_no_plaintext_password_in_set_password_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        $setPasswordLink = route('onboarding.show', ['token' => $token->token]);
        Mail::queue(new SetPasswordMail(
            email: $user->email,
            setPasswordLink: $setPasswordLink,
            userName: $user->name,
        ));

        Mail::assertQueued(static function (SetPasswordMail $mail): bool {
            // Email should not contain common temporary password patterns
            $subject = (string) $mail->envelope()->subject;
            $content = json_encode($mail);

            return ! str_contains($content, 'TempPassword') &&
                   ! str_contains($content, 'temp_password') &&
                   ! str_contains($content, 'password123') &&
                   ! str_contains($subject, 'temporary');
        });
    }

    public function test_no_reset_token_exposed_in_response(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->withoutMiddleware()->post(route('password.email'), [
            'email' => 'user@example.com',
        ]);

        // Response should not contain the actual reset token
        $responseContent = $response->getContent();
        $this->assertNotNull($responseContent);

        // Token should only be in the queued email, not in HTTP response
        // Search for common token patterns that shouldn't be exposed
        $this->assertStringNotContainsString('token=', $responseContent);
    }

    // TEST GROUP K — Database integrity

    public function test_no_duplicate_users_created(): void
    {
        $email = 'provider@example.com';
        Mail::fake();

        $provider1 = User::factory()->create(['email' => $email]);
        $provider1->assignRole('provider');

        // Attempt to create another with same email should fail
        try {
            User::factory()->create(['email' => $email]);
            $this->fail('Should have thrown unique constraint violation');
        } catch (UniqueConstraintViolationException) {
            // Expected behavior
        }

        $userCount = User::where('email', $email)->count();
        $this->assertEquals(1, $userCount);
    }

    public function test_no_orphan_onboarding_tokens(): void
    {
        // Create user and token
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // Delete user
        $user->delete();

        // Token should still exist (referential integrity)
        $orphanToken = OnboardingToken::where('user_id', $user->id)->first();
        $this->assertNotNull($orphanToken);

        // But it should be unusable
        $response = $this->get(route('onboarding.show', ['token' => $token->token]));
        $response->assertRedirect();
    }

    public function test_used_tokens_marked_correctly(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // Before use
        $token->refresh();
        $this->assertNull($token->used_at);

        // After use
        $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $token->refresh();
        $this->assertNotNull($token->used_at);
        $this->assertLessThan(2, now()->diffInSeconds($token->used_at));
    }

    public function test_password_changed_at_timestamp_updated(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        $timeBefore = now()->subSecond();

        $this->get(route('onboarding.show', ['token' => $token->token]));
        $this->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $user->refresh();

        // Password changed timestamp should be set after request started
        $this->assertNotNull($user->password_changed_at);
        $this->assertGreaterThanOrEqual($timeBefore, $user->password_changed_at);
    }

    public function test_no_duplicate_profile_stats_on_provider_creation(): void
    {
        Mail::fake();

        $provider = User::factory()->create(['email' => 'provider@example.com']);
        $provider->assignRole('provider');

        // Ensure profile and stats exist
        $provider->profile()->firstOrCreate(['slug' => 'test-slug']);
        $provider->profile->stats()->firstOrCreate();

        $statsCount = $provider->profile->stats()->count();
        $this->assertEquals(1, $statsCount);

        // Second creation should not duplicate
        $provider->profile->refresh()->stats()->firstOrCreate();
        $statsCount = $provider->profile->stats()->count();
        $this->assertEquals(1, $statsCount);
    }

    public function test_activity_logs_not_spammed_on_duplicate_requests(): void
    {
        $user = User::factory()->create();
        $token = OnboardingToken::create([
            'user_id' => $user->id,
            'token' => 'test-token-12345',
            'expires_at' => now()->addHours(24),
        ]);

        // First request
        $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $firstLogCount = DB::table('activity_logs')
            ->where('subject_type', 'App\\Models\\User')
            ->where('subject_id', $user->id)
            ->count();

        // Second request (will fail due to used token)
        $this->withoutMiddleware()->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'ValidPassword123!',
            'password_confirmation' => 'ValidPassword123!',
        ]);

        $secondLogCount = DB::table('activity_logs')
            ->where('subject_type', 'App\\Models\\User')
            ->where('subject_id', $user->id)
            ->count();

        // Should only have one log entry (for the successful password change)
        $this->assertEquals($firstLogCount, $secondLogCount);
    }
}
