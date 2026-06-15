<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class GoogleAuthService
{
    public function getGoogleUser(): SocialiteUser
    {
        return Socialite::driver('google')->user();
    }

    public function findOrCreateUser(SocialiteUser $googleUser): User
    {
        $googleId = (string) $googleUser->getId();
        $email = mb_strtolower((string) $googleUser->getEmail());

        if ($googleId === '' || $email === '') {
            throw new \RuntimeException('Google account did not provide the required identity fields.');
        }

        $user = User::query()->where('google_id', $googleId)->first();

        if ($user) {
            $this->ensurePublicUserCanUseGoogle($user);

            return $user;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $this->ensurePublicUserCanUseGoogle($user);

            if (filled($user->google_id) && $user->google_id !== $googleId) {
                throw new \RuntimeException('This email is already linked to another Google account.');
            }

            $user->forceFill([
                'google_id' => $googleId,
                'oauth_provider' => 'google',
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            return $user;
        }

        return $this->createGoogleUser($googleUser, $googleId, $email);
    }

    private function createGoogleUser(SocialiteUser $googleUser, string $googleId, string $email): User
    {
        return User::query()->create([
            'name' => $googleUser->getName() ?: $email,
            'email' => $email,
            'password' => str()->random(64),
            'google_id' => $googleId,
            'oauth_provider' => 'google',
            'email_verified_at' => now(),
            'is_active' => true,
            'is_suspended' => false,
        ]);
    }

    public function assignUserRole(User $user): void
    {
        $this->ensurePublicUserCanUseGoogle($user);

        if (! $user->hasRole('user')) {
            $user->assignRole('user');
        }
    }

    private function ensurePublicUserCanUseGoogle(User $user): void
    {
        if ($user->hasRole('provider') || $user->hasRole('super_admin')) {
            throw new \RuntimeException('Google login is not allowed for this account role.');
        }
    }
}
