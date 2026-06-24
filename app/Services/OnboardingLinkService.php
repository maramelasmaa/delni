<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OnboardingToken;
use App\Models\User;

class OnboardingLinkService
{
    public function createOrRefreshLink(User $user): string
    {
        if (! $user->hasRole('provider')) {
            throw new \InvalidArgumentException('User is not a provider');
        }

        OnboardingToken::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        $plainTextToken = OnboardingToken::generatePlainTextToken();

        OnboardingToken::query()->create([
            'user_id' => $user->id,
            'token' => OnboardingToken::hashToken($plainTextToken),
            'expires_at' => now()->addHours(72),
        ]);

        return route('onboarding.show', ['token' => $plainTextToken]);
    }

    public function canGenerate(User $user): bool
    {
        return $user->hasRole('provider');
    }
}
