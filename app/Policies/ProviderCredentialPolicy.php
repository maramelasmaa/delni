<?php

namespace App\Policies;

use App\Models\ProviderCredential;
use App\Models\User;

class ProviderCredentialPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('provider') && $user->profile !== null;
    }

    public function view(User $user, ProviderCredential $credential): bool
    {
        return $user->hasRole('provider')
            && $credential->profile?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('provider') && $user->profile !== null;
    }

    public function update(User $user, ProviderCredential $credential): bool
    {
        return $user->hasRole('provider')
            && $credential->profile?->user_id === $user->id;
    }

    public function delete(User $user, ProviderCredential $credential): bool
    {
        return $user->hasRole('provider')
            && $credential->profile?->user_id === $user->id;
    }
}
