<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;

class SuperAdminGuardService
{
    /**
     * Prevent assignment of second super_admin or changes that would leave zero admins.
     */
    public static function guardAgainstMultipleSuperAdmins(?User $userBeingModified = null): void
    {
        $superAdminCount = User::role('super_admin')->count();

        if ($superAdminCount > 1) {
            throw new \RuntimeException('CRITICAL: Multiple super_admin roles detected. Only one super_admin should exist.');
        }
    }

    /**
     * Prevent assigning super_admin role to a user.
     * The super_admin role can only be assigned through the dedicated ensure-super-admin command.
     */
    public static function preventSuperAdminAssignment(User $user, string $roleName): string
    {
        if ($roleName === 'super_admin') {
            throw new \LogicException(
                'Super admin role cannot be assigned through this interface. '.
                'Use: php artisan delni:ensure-super-admin'
            );
        }

        return $roleName;
    }

    /**
     * Prevent deletion of the sole super_admin user.
     */
    public static function canDeleteUser(User $user): bool
    {
        // If user is super_admin and is the only one, prevent deletion
        if ($user->hasRole('super_admin')) {
            $superAdminCount = User::role('super_admin')->count();
            if ($superAdminCount === 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prevent bulk deletion of super_admin users.
     */
    public static function canBulkDeleteUsers(array $userIds): bool
    {
        $superAdminCount = User::role('super_admin')->whereIn('id', $userIds)->count();
        $totalSuperAdmins = User::role('super_admin')->count();

        // If any super_admin is in the bulk delete and it would leave zero admins, prevent it
        if ($superAdminCount > 0 && $superAdminCount === $totalSuperAdmins) {
            return false;
        }

        return true;
    }

    /**
     * Get the current super_admin user, or null if none exists.
     */
    public static function getCurrentSuperAdmin(): ?User
    {
        return User::role('super_admin')->first();
    }

    /**
     * Verify exactly one super_admin exists.
     * Throw if count is not exactly 1.
     */
    public static function verify(): void
    {
        $count = User::role('super_admin')->count();

        if ($count === 0) {
            throw new \RuntimeException('No super_admin user found. Run: php artisan delni:ensure-super-admin');
        }

        if ($count > 1) {
            throw new \RuntimeException('Multiple super_admin users found. This is a critical state. Only one super_admin should exist.');
        }
    }
}
