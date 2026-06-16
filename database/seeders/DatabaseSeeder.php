<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Production-only: roles and admin setup
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Test data (dev/staging only — uncomment to use)
        // $this->call([MalamProviderSeeder::class]);
    }
}
