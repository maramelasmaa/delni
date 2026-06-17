<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL');
        $name = env('SUPER_ADMIN_NAME');
        $password = env('SUPER_ADMIN_PASSWORD');

        if (! $name || ! $email || ! $password) {
            throw new \RuntimeException('SUPER_ADMIN_NAME, SUPER_ADMIN_EMAIL, and SUPER_ADMIN_PASSWORD must be set before seeding an admin user.');
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'phone' => null,
                'is_active' => true,
                'is_suspended' => false,
                'email_verified_at' => now(),
            ]
        );

        // Assign role only if not already assigned — U1: exactly one role
        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }
    }
}
