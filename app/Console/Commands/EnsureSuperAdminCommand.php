<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SuperAdminGuardService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EnsureSuperAdminCommand extends Command
{
    protected $signature = 'delni:ensure-super-admin
                            {--email= : Super admin email address}
                            {--name= : Super admin display name}
                            {--password= : Super admin password (avoid shell history in production)}
                            {--reset-password : Explicitly rotate the existing super admin password}';

    protected $description = 'Ensure exactly one super admin user exists.';

    public function handle(): int
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminCount = User::role('super_admin')->count();

        if ($superAdminCount > 1) {
            $this->error('CRITICAL: Multiple super_admin users already exist. Resolve this manually before continuing.');

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) ($this->option('email') ?: config('app.super_admin_email'))));
        $name = trim((string) ($this->option('name') ?: config('app.super_admin_name')));
        $password = $this->option('password') ?: config('app.super_admin_password');

        if ($email === '') {
            $this->error('A super admin email is required via --email or SUPER_ADMIN_EMAIL.');

            return self::FAILURE;
        }

        if ($name === '') {
            $this->error('A super admin name is required via --name or SUPER_ADMIN_NAME.');

            return self::FAILURE;
        }

        $existingSuperAdmin = User::role('super_admin')->first();

        if ($existingSuperAdmin && mb_strtolower($existingSuperAdmin->email) !== $email) {
            $this->error(sprintf(
                'A different super admin already exists: %s. This command will not replace or demote that user.',
                $existingSuperAdmin->email
            ));

            return self::FAILURE;
        }

        $existingUser = User::withTrashed()->where('email', $email)->first();

        if ($existingUser?->trashed()) {
            $this->error(sprintf(
                'A soft-deleted user already exists with email %s. Restore or permanently remove that account before creating the super admin.',
                $email
            ));

            return self::FAILURE;
        }

        $creatingUser = $existingUser === null;

        if ($creatingUser && blank($password)) {
            if (! $this->input->isInteractive()) {
                $this->error('A password is required via --password or SUPER_ADMIN_PASSWORD when creating the super admin non-interactively.');

                return self::FAILURE;
            }

            $password = $this->secret('Enter the super admin password');
        }

        if ($this->option('reset-password') && blank($password)) {
            if (! $this->input->isInteractive()) {
                $this->error('Password reset requested but no password was provided via --password or SUPER_ADMIN_PASSWORD.');

                return self::FAILURE;
            }

            $password = $this->secret('Enter the new super admin password');
        }

        Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $user = $existingUser;

        if ($user === null) {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make((string) $password),
                'security_flagged' => false,
                'is_active' => true,
                'is_suspended' => false,
                'email_verified_at' => now(),
            ]);

            $this->info(sprintf('Created super admin user record for %s', $user->email));
        } else {
            if ($user->name !== $name) {
                $this->warn(sprintf(
                    'Existing user name is "%s"; requested name "%s" was not applied automatically.',
                    $user->name,
                    $name
                ));
            }

            if ($this->option('reset-password')) {
                $user->forceFill([
                    'password' => Hash::make((string) $password),
                    'password_changed_at' => now(),
                ])->save();

                $this->info(sprintf('Rotated password for %s', $user->email));
            }
        }

        if (! $user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
            $this->info(sprintf('Assigned super_admin role to %s', $user->email));
        } else {
            $this->info(sprintf('User %s already has the super_admin role', $user->email));
        }

        try {
            SuperAdminGuardService::verify();
            $this->info('Super admin enforcement check passed.');
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info(sprintf('Super admin configured: %s (%s)', $user->name, $user->email));

        return self::SUCCESS;
    }
}
