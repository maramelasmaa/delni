<?php

namespace App\Policies;

use App\Models\Subcategory;
use App\Models\User;

class SubcategoryPolicy
{
    /**
     * Admin bypasses all subcategory checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Admin can view subcategory listing in Filament.
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

    public function update(User $user, Subcategory $subcategory): bool
    {
        return false;
    }

    public function delete(User $user, Subcategory $subcategory): bool
    {
        return false;
    }
}
