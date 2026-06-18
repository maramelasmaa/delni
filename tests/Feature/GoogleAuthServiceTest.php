<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_account_cannot_be_linked_to_public_google_login_by_email(): void
    {
        $provider = User::factory()->create([
            'email' => 'provider@example.com',
            'google_id' => null,
        ]);
        $provider->assignRole('provider');

        try {
            app(GoogleAuthService::class)->findOrCreateUser(
                $this->googleUser(id: 'google-provider-id', email: 'provider@example.com'),
            );

            $this->fail('Provider account was allowed to link to public Google login.');
        } catch (\RuntimeException) {
            //
        }

        $provider->refresh();

        $this->assertNull($provider->google_id);
        $this->assertFalse($provider->hasRole('user'));
    }

    public function test_provider_account_cannot_be_logged_in_from_public_google_login_by_google_id(): void
    {
        $provider = User::factory()->create([
            'email' => 'provider@example.com',
            'google_id' => 'google-provider-id',
        ]);
        $provider->assignRole('provider');

        try {
            app(GoogleAuthService::class)->findOrCreateUser(
                $this->googleUser(id: 'google-provider-id', email: 'provider@example.com'),
            );

            $this->fail('Provider account was allowed through public Google login.');
        } catch (\RuntimeException) {
            //
        }

        $provider->refresh();

        $this->assertFalse($provider->hasRole('user'));
    }

    public function test_admin_account_cannot_be_linked_to_public_google_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'google_id' => null,
        ]);
        $admin->assignRole('super_admin');

        try {
            app(GoogleAuthService::class)->findOrCreateUser(
                $this->googleUser(id: 'google-admin-id', email: 'admin@example.com'),
            );

            $this->fail('Admin account was allowed to link to public Google login.');
        } catch (\RuntimeException) {
            //
        }

        $admin->refresh();

        $this->assertNull($admin->google_id);
        $this->assertFalse($admin->hasRole('user'));
    }

    public function test_public_user_can_be_linked_to_google_login(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'google_id' => null,
        ]);
        $user->assignRole('user');

        $linkedUser = app(GoogleAuthService::class)->findOrCreateUser(
            $this->googleUser(id: 'google-user-id', email: 'user@example.com'),
        );

        $this->assertTrue($linkedUser->is($user));
        $this->assertSame('google-user-id', $linkedUser->google_id);
        $this->assertTrue($linkedUser->hasRole('user'));
    }

    private function googleUser(string $id, string $email, ?string $name = 'Google User'): SocialiteUser
    {
        return (new SocialiteUser)->map([
            'id' => $id,
            'email' => $email,
            'name' => $name,
        ]);
    }
}
