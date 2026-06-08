<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * DEPRECATED: Use AdminUserSeeder or php artisan delni:ensure-super-admin instead.
     * This seeder is kept for backward compatibility but should not be called.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'super_admin']);

        $email = env('SUPER_ADMIN_EMAIL', 'admin@example.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'password');

        $adminExists = User::role('super_admin')->exists();

        if (! $adminExists) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make($password),
                    'security_flagged' => false,
                    'is_active' => true,
                    'is_suspended' => false,
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole('super_admin')) {
                $user->assignRole($role);
            }
        }
    }
}
