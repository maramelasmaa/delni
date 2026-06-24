<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('user', 'web');
    }

    // ─── Register ────────────────────────────────────────────────────────────

    public function test_user_can_register_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ahmed Ali',
            'email' => 'ahmed@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['success', 'message', 'data' => ['token', 'user' => ['id', 'name', 'email', 'is_provider']]]);

        $this->assertDatabaseHas(User::class, ['email' => 'ahmed@example.com']);
    }

    public function test_register_assigns_user_role(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ahmed Ali',
            'email' => 'ahmed@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ]);

        $user = User::where('email', 'ahmed@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_register_requires_all_fields(): void
    {
        $this->postJson('/api/v1/auth/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ahmed',
            'email' => 'existing@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_rejects_weak_password(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_rejects_mismatched_password_confirmation(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Different123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    // ─── Login ───────────────────────────────────────────────────────────────

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $user->assignRole('user');

        $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->assertOk()
            ->assertJsonStructure(['success', 'data' => ['token', 'user' => ['id', 'name', 'email']]]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertSame(1, $user->fresh()->failed_login_attempts);
        $this->assertNotNull($user->fresh()->last_failed_login_at);
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_for_inactive_user(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'is_active' => false,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ])->assertUnprocessable();
    }

    public function test_login_fails_for_suspended_user(): void
    {
        User::factory()->create([
            'email' => 'suspended@example.com',
            'is_suspended' => true,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'suspended@example.com',
            'password' => 'password',
        ])->assertUnprocessable();
    }

    public function test_login_fails_for_locked_account(): void
    {
        User::factory()->create([
            'email' => 'locked@example.com',
            'locked_until' => now()->addMinutes(10),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'locked@example.com',
            'password' => 'password',
        ])->assertUnprocessable();
    }

    public function test_login_success_clears_failed_attempt_counters(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'failed_login_attempts' => 7,
            'last_failed_login_at' => now()->subMinute(),
            'locked_until' => now()->subMinute(),
        ]);
        $user->assignRole('user');

        $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->assertOk();

        $user->refresh();

        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertNull($user->last_failed_login_at);
        $this->assertNull($user->locked_until);
    }

    // ─── Me ──────────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_fetch_own_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_unauthenticated_request_to_me_returns_401(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'يرجى تسجيل الدخول أولاً.',
            ]);
    }

    // ─── Logout ──────────────────────────────────────────────────────────────

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $newToken = $user->createToken('test');

        $this->withHeader('Authorization', 'Bearer '.$newToken->plainTextToken)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonFragment(['message' => 'تم تسجيل الخروج بنجاح.']);

        // Token record must be deleted from the database
        $this->assertModelMissing($newToken->accessToken);
    }

    public function test_unauthenticated_request_to_logout_returns_401(): void
    {
        $this->postJson('/api/v1/auth/logout')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'يرجى تسجيل الدخول أولاً.',
            ]);
    }

    // ─── Forgot Password ─────────────────────────────────────────────────────

    public function test_forgot_password_sends_notification_for_existing_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'user@example.com'])
            ->assertOk()
            ->assertJsonFragment(['message' => 'إذا كان البريد الإلكتروني مسجلاً، فستتلقى رابطاً لإعادة تعيين كلمة المرور قريباً.']);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_returns_same_success_for_unknown_email(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'nobody@example.com'])
            ->assertOk()
            ->assertJsonFragment(['message' => 'إذا كان البريد الإلكتروني مسجلاً، فستتلقى رابطاً لإعادة تعيين كلمة المرور قريباً.']);

        Notification::assertNothingSent();
    }

    public function test_forgot_password_requires_email(): void
    {
        $this->postJson('/api/v1/auth/forgot-password', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    // ─── Reset Password ──────────────────────────────────────────────────────

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'user@example.com',
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ])->assertOk()
            ->assertJsonFragment(['message' => 'تم إعادة تعيين كلمة المرور بنجاح.']);
    }

    public function test_reset_password_revokes_all_existing_tokens(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $existingToken = $user->createToken('device-old')->plainTextToken;
        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'user@example.com',
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ])->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$existingToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'user@example.com',
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ])->assertUnprocessable();
    }

    public function test_reset_password_requires_all_fields(): void
    {
        $this->postJson('/api/v1/auth/reset-password', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    public function test_reset_password_rejects_weak_password(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'user@example.com',
            'password' => 'weakpass',
            'password_confirmation' => 'weakpass',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    // ─── Delete Account ──────────────────────────────────────────────────────

    public function test_authenticated_user_can_delete_account(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/auth/account');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'تم حذف الحساب بنجاح.',
            ]);

        // Assert user is soft-deleted
        $this->assertSoftDeleted(User::class, ['id' => $user->id]);

        // Assert token is deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_account(): void
    {
        $this->deleteJson('/api/v1/auth/account')
            ->assertUnauthorized();
    }
}
