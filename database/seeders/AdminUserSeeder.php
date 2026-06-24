<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        throw new \RuntimeException(
            'AdminUserSeeder is disabled for safety. Create the sole super admin via: php artisan delni:ensure-super-admin'
        );
    }
}
