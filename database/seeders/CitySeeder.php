<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Tripoli',      'name_ar' => 'طرابلس'],
            ['name' => 'Benghazi',     'name_ar' => 'بنغازي'],
            ['name' => 'Misrata',      'name_ar' => 'مصراتة'],
            ['name' => 'Zawiya',       'name_ar' => 'الزاوية'],
            ['name' => 'Zliten',       'name_ar' => 'زليتن'],
            ['name' => 'Bayda',        'name_ar' => 'البيضاء'],
            ['name' => 'Tobruk',       'name_ar' => 'طبرق'],
            ['name' => 'Sabha',        'name_ar' => 'سبها'],
            ['name' => 'Sirte',        'name_ar' => 'سرت'],
            ['name' => 'Derna',        'name_ar' => 'درنة'],
            ['name' => 'Khoms',        'name_ar' => 'الخمس'],
            ['name' => 'Zintan',       'name_ar' => 'الزنتان'],
            ['name' => 'Gharyan',      'name_ar' => 'غريان'],
            ['name' => 'Tarhuna',      'name_ar' => 'ترهونة'],
            ['name' => 'Bani Walid',   'name_ar' => 'بني وليد'],
        ];

        foreach ($cities as $order => $city) {
            City::firstOrCreate(
                ['slug' => Str::slug($city['name'])],
                [
                    'name' => $city['name'],
                    'name_ar' => $city['name_ar'],
                    'sort_order' => $order,
                    'is_active' => true,
                ]
            );
        }
    }
}
