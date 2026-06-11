<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class GoogleAuthService
{
    public function getGoogleUser(): GoogleProvider
    {
        return Socialite::driver('google')->user();
    }

    public function findOrCreateUser(GoogleProvider $googleUser): User
    {
        $user = User::where('google_id', $googleUser->id)->first();

        if ($user) {
            return $user;
        }

        return $this->createGoogleUser($googleUser);
    }

    private function createGoogleUser(GoogleProvider $googleUser): User
    {
        return User::create([
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'google_id' => $googleUser->id,
            'oauth_provider' => 'google',
            'email_verified_at' => now(),
            'is_active' => true,
            'is_suspended' => false,
        ]);
    }

    public function assignUserRole(User $user): void
    {
        if (! $user->hasRole('user')) {
            $user->assignRole('user');
        }
    }
}
