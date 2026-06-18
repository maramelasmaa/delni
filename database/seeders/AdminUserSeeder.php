<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.super_admin_email');
        $name = config('app.super_admin_name');
        $password = config('app.super_admin_password');

        if (! $name || ! $email || ! $password) {
            throw new \RuntimeException('SUPER_ADMIN_NAME, SUPER_ADMIN_EMAIL, and SUPER_ADMIN_PASSWORD must be configured before seeding the super admin user.');
        }

        $superAdmin = User::firstOrCreate(
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

        if (! $superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }
    }
}
