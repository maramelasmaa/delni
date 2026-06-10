<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProviderChatSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create cities
        $cities = [
            City::firstOrCreate(['slug' => 'tripoli'], ['name' => 'Tripoli', 'name_ar' => 'طرابلس', 'is_active' => true]),
            City::firstOrCreate(['slug' => 'benghazi'], ['name' => 'Benghazi', 'name_ar' => 'بنغازي', 'is_active' => true]),
            City::firstOrCreate(['slug' => 'misrata'], ['name' => 'Misrata', 'name_ar' => 'مصراتة', 'is_active' => true]),
            City::firstOrCreate(['slug' => 'tobruk'], ['name' => 'Tobruk', 'name_ar' => 'طبرق', 'is_active' => true]),
        ];

        // Get or create categories
        $categories = [
            Category::firstOrCreate(['slug' => 'law-legal-services'], ['name' => 'Legal Services', 'name_ar' => 'الخدمات القانونية', 'is_active' => true]),
            Category::firstOrCreate(['slug' => 'hvac-air-conditioning'], ['name' => 'HVAC Services', 'name_ar' => 'خدمات التكييف', 'is_active' => true]),
            Category::firstOrCreate(['slug' => 'photography-videography'], ['name' => 'Photography', 'name_ar' => 'التصوير', 'is_active' => true]),
            Category::firstOrCreate(['slug' => 'construction-contracting'], ['name' => 'Construction', 'name_ar' => 'البناء', 'is_active' => true]),
        ];

        // Get subscription plan
        $plan = SubscriptionPlan::firstOrCreate(
            ['name' => 'Basic Plan'],
            [
                'name_ar' => 'الخطة الأساسية',
                'duration_months' => 3,
                'price_lyd' => 50,
                'is_active' => true,
                'tier' => 'basic',
            ]
        );

        // Create 20 providers across different cities
        $names = [
            'محامي عبدالعزيز',
            'فني أحمد التكييف',
            'مصور علي الأفراح',
            'مقاول حسن البناء',
            'محامي فاطمة',
            'فني محمود الكهرباء',
            'مصور يوسف',
            'مقاول سالم',
            'محامي ليلى',
            'فني كريم',
            'مصور عمر',
            'مقاول طارق',
            'محامي نور',
            'فني رائد',
            'مصور بشار',
            'مقاول ياسر',
            'محامي ريم',
            'فني زياد',
            'مصور إبراهيم',
            'مقاول خالد',
        ];

        $descriptions = [
            'متخصص في القانون الإداري والتجاري',
            'فني متمرس في صيانة أجهزة التكييف',
            'مصور محترف للحفلات والأعراس',
            'مقاول بخبرة 15 سنة',
            'محام متخصص في قانون العمل',
            'فني كهربائي معتمد',
            'مصور حفلات متمرس',
            'مقاول بناء ومقاولات عامة',
        ];

        for ($i = 0; $i < 20; $i++) {
            $user = User::factory()->create([
                'name' => $names[$i],
                'is_active' => true,
                'is_suspended' => false,
            ]);

            $user->assignRole('provider');

            // Create subscription
            $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'starts_at' => Carbon::today(),
                'ends_at' => Carbon::today()->addMonths(3),
                'is_active' => true,
            ]);

            $city = $cities[$i % count($cities)];
            $category = $categories[$i % count($categories)];

            $profile = Profile::factory()
                ->for($user)
                ->for($category)
                ->for($city)
                ->create([
                    'is_complete' => true,
                    'business_name' => $names[$i],
                    'bio' => $descriptions[$i % count($descriptions)],
                ]);

            // Create profile stats for search visibility
            ProfileStats::factory()->for($profile)->create();
        }

        $this->command->info('✅ Created 20 providers across 4 cities');
    }
}
