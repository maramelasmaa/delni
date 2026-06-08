<?php

namespace Database\Seeders;

use App\Models\ContactInfo;
use Illuminate\Database\Seeder;

class ContactInfoSeeder extends Seeder
{
    public function run(): void
    {
        ContactInfo::firstOrCreate(
            [],
            [
                'whatsapp' => '218912345678',
                'phone' => '+218 (0)21 123 4567',
                'email' => 'contact@delni.example',
                'address' => 'Tripoli, Libya',
            ]
        );
    }
}
