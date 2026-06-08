<?php

namespace App\Policies;

use App\Models\City;
use App\Models\User;

class CityPolicy
{
    /**
     * Admin bypasses all city checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Admin can view city listing in Filament.
     * Admin handled by before().
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, City $city): bool
    {
        return false;
    }

    public function delete(User $user, City $city): bool
    {
        return false;
    }
}
