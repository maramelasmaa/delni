<?php

namespace App\Policies;

use App\Models\User;
use App\Services\SuperAdminGuardService;

class UserPolicy
{
    /**
     * Admin bypasses all user checks.
     * Admin deleting/suspending users is allowed per SRS — A1.
     * Except: Cannot delete the sole super_admin.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            // Prevent deleting the sole super_admin
            if ($ability === 'delete' && ! SuperAdminGuardService::canDeleteUser($user)) {
                return false;
            }

            return true;
        }

        return null;
    }

    /**
     * Admin can view user listing in Filament.
     * Admin handled by before().
     * Non-admins cannot list users.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Users can only view their own account.
     * Admin handled by before().
     */
    public function view(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }

    /**
     * Only admin can create provider accounts.
     * Admin handled by before().
     * Invariant: U2.
     */
    public function createProvider(User $user): bool
    {
        return false;
    }

    /**
     * Only admin can create admin accounts.
     * Admin handled by before().
     * Invariant: U2.
     */
    public function createAdmin(User $user): bool
    {
        return false;
    }

    /**
     * Users can update their own basic info only.
     * Admin handled by before().
     * Role immutability enforced in Form Request — not here.
     * Invariant: U6.
     */
    public function update(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }

    /**
     * Only admin can soft-delete users.
     * Admin handled by before().
     */
    public function delete(User $user, User $target): bool
    {
        return false;
    }

    /**
     * Only admin can suspend users.
     * Admin handled by before().
     * Invariants: A1, U3.
     */
    public function suspend(User $user, User $target): bool
    {
        return false;
    }

    /**
     * Only admin can reinstate users.
     * Admin handled by before().
     * Invariant: A3.
     */
    public function reinstate(User $user, User $target): bool
    {
        return false;
    }
}
