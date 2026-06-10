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
        // Seed core app data
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            SubscriptionPlanSeeder::class,
            ProviderTypeIconSeeder::class,
        ]);
    }
}
