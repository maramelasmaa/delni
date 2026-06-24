<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SafeProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ProviderTypesSeeder::class,
        ]);
    }
}
