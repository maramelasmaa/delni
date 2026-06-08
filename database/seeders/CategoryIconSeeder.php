<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryIconSeeder extends Seeder
{
    public function run(): void
    {
        $icons = [
            1 => 'heroicon-o-palette',              // Graphic Design
            2 => 'heroicon-o-building-office',      // Construction
            3 => 'heroicon-o-code-bracket',         // Tech & Software
            4 => 'heroicon-o-camera',               // Photography
            5 => 'heroicon-o-scale-balance',        // Legal & Accounting
            6 => 'heroicon-o-truck',                // Auto & Mechanics
            7 => 'heroicon-o-heart',                // Medical & Health
            8 => 'heroicon-o-archive-box',          // Logistics
            9 => 'heroicon-o-wine-glass',           // Catering
            10 => 'heroicon-o-wrench-screwdriver',  // Maintenance
        ];

        foreach ($icons as $id => $icon) {
            DB::table('categories')
                ->where('id', $id)
                ->update(['icon' => $icon]);
        }

        $this->command->info('✅ Category icons updated!');
    }
}
