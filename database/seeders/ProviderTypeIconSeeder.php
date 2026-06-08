<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderTypeIconSeeder extends Seeder
{
    public function run(): void
    {
        $icons = [
            'individual' => 'heroicon-o-user-circle',
            'company' => 'heroicon-o-building-office',
            'agency' => 'heroicon-o-briefcase',
            'clinic' => 'heroicon-o-heart',
            'studio' => 'heroicon-o-palette',
            'freelancer' => 'heroicon-o-computer-desktop',
            'other' => 'heroicon-o-square-3-stack-3d',
        ];

        foreach ($icons as $code => $icon) {
            DB::table('provider_types')
                ->where('code', $code)
                ->update(['icon' => $icon]);
        }

        $this->command->info('✅ Provider type icons seeded!');
    }
}
