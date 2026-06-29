<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordWebTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_the_reset_page_renders_with_token_and_email(): void
    {
        $this->get(route('password.reset', ['token' => 'sample-token', 'email' => 'user@example.com']))
            ->assertOk()
            ->assertSee(__('auth.reset_password_title'))
            ->assertSee('user@example.com');
    }

    public function test_it_resets_the_password_with_a_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::createToken($user);

        $this->post(route('password.reset.update'), [
            'token' => $token,
            'email' => 'user@example.com',
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ])
            ->assertOk()
            ->assertSee(__('auth.reset_password_success_title'));

        $this->assertTrue(Hash::check('NewPass123', $user->fresh()->password));
    }

    public function test_it_resets_the_password_when_email_is_locked_in_the_link(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::createToken($user);

        $this->post(route('password.reset.update', ['token' => $token, 'email' => 'user@example.com']), [
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ])
            ->assertOk()
            ->assertSee(__('auth.reset_password_success_title'));

        $this->assertTrue(Hash::check('NewPass123', $user->fresh()->password));
    }

    public function test_it_rejects_an_invalid_token_and_keeps_the_old_password(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('OldPass123'),
        ]);

        $this->from(route('password.reset', ['token' => 'x', 'email' => 'user@example.com']))
            ->post(route('password.reset.update'), [
                'token' => 'invalid-token',
                'email' => 'user@example.com',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('OldPass123', $user->fresh()->password));
    }

    public function test_it_validates_password_strength(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = Password::createToken($user);

        $this->from(route('password.reset', ['token' => $token, 'email' => 'user@example.com']))
            ->post(route('password.reset.update'), [
                'token' => $token,
                'email' => 'user@example.com',
                'password' => 'weak',
                'password_confirmation' => 'weak',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('password');
    }
}
