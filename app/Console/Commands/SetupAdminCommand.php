<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SetupAdminCommand extends Command
{
    protected $signature = 'delni:setup-admin {--force}';

    protected $description = 'Setup initial admin user from ADMIN_EMAIL and ADMIN_PASSWORD environment variables (Railway/deployment)';

    public function handle(): int
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->error('ADMIN_EMAIL and ADMIN_PASSWORD environment variables required');

            return self::FAILURE;
        }

        // Ensure admin role exists
        Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web']
        );

        // Check if admin already exists
        if (User::where('email', $email)->exists() && ! $this->option('force')) {
            $this->info("Admin user already exists: {$email}");

            return self::SUCCESS;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'is_active' => true,
                'is_suspended' => false,
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole('admin')) {
            $user->assignRole('admin');
            $this->info("✓ Assigned admin role to {$user->email}");
        }

        $this->info("✓ Admin user ready: {$user->email}");

        return self::SUCCESS;
    }
}
