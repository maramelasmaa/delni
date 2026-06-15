<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OnboardingToken;
use App\Models\User;
use Illuminate\Support\Str;

class OnboardingLinkService
{
    public function createOrRefreshLink(User $user): string
    {
        if (! $user->hasRole('provider')) {
            throw new \InvalidArgumentException('User is not a provider');
        }

        // Create new token or extend existing unused one
        $onboardingToken = OnboardingToken::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->first();

        if ($onboardingToken) {
            // Extend existing unused token
            $onboardingToken->update(['expires_at' => now()->addHours(72)]);
        } else {
            // Create new token
            $onboardingToken = OnboardingToken::create([
                'user_id' => $user->id,
                'token' => Str::random(60),
                'expires_at' => now()->addHours(72),
            ]);
        }

        return route('onboarding.show', ['token' => $onboardingToken->token]);
    }

    public function canGenerate(User $user): bool
    {
        return $user->hasRole('provider');
    }
}
