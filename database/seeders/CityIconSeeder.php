<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CityIconSeeder extends Seeder
{
    public function run(): void
    {
        // All cities get the same map pin icon
        $icon = 'heroicon-o-map-pin';

        DB::table('cities')->update(['icon' => $icon]);

        $this->command->info('✅ City icons updated!');
    }
}
