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
            CategorySeeder::class,
            CitySeeder::class,
        ]);

        // Optional: Seed marketplace test data with diverse providers and placements
        if ($this->command->confirm('Seed marketplace placement test data? (for testing ranking system)', true)) {
            $this->call(MarketplacePlacementSeeder::class);
        }
    }
}
