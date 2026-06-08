<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SuperAdminGuardService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EnsureSuperAdminCommand extends Command
{
    protected $signature = 'delni:ensure-super-admin {--force}';

    protected $description = 'Ensure exactly one super admin user exists. Reads from env: SUPER_ADMIN_NAME, SUPER_ADMIN_EMAIL, SUPER_ADMIN_PASSWORD.';

    public function handle(): int
    {
        // Verify role exists
        $role = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web']
        );

        $email = config('app.super_admin_email') ?? env('SUPER_ADMIN_EMAIL');
        $name = config('app.super_admin_name') ?? env('SUPER_ADMIN_NAME', 'Super Admin');
        $password = env('SUPER_ADMIN_PASSWORD');

        if (! $email) {
            $this->error('SUPER_ADMIN_EMAIL is not set in .env');

            return self::FAILURE;
        }

        if (! $password) {
            $this->error('SUPER_ADMIN_PASSWORD is not set in .env');

            return self::FAILURE;
        }

        // Check if a different super_admin already exists
        $existingSuperAdmin = User::role('super_admin')->first();

        if ($existingSuperAdmin && $existingSuperAdmin->email !== $email && ! $this->option('force')) {
            $this->error(sprintf(
                'Super admin already exists with email: %s. Use --force to override.',
                $existingSuperAdmin->email
            ));

            return self::FAILURE;
        }

        // If different super_admin exists with --force, remove role from old one
        if ($existingSuperAdmin && $existingSuperAdmin->email !== $email && $this->option('force')) {
            $existingSuperAdmin->removeRole('super_admin');
            $this->warn(sprintf('Removed super_admin role from %s', $existingSuperAdmin->email));
        }

        // Find or create user with email
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'security_flagged' => false,
                'is_active' => true,
                'is_suspended' => false,
                'email_verified_at' => now(),
            ]
        );

        // Ensure user has super_admin role
        if (! $user->hasRole('super_admin')) {
            $user->assignRole($role);
            $this->info(sprintf('Assigned super_admin role to %s', $user->email));
        } else {
            $this->info(sprintf('User %s already has super_admin role', $user->email));
        }

        // Verify exactly one super_admin exists
        try {
            SuperAdminGuardService::verify();
            $this->info('✓ Super admin enforcement check passed');
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('✓ Super admin configured: %s (%s)', $user->name, $user->email));

        return self::SUCCESS;
    }
}
