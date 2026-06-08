<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic Monthly',
                'name_ar' => 'أساسي شهري',
                'duration_months' => 1,
                'price_lyd' => 50.00,
                'is_active' => true,
                'tier' => 'basic',
                'featured_days_per_subscription' => 0,
                'includes_homepage_featured' => false,
                'includes_top_search' => false,
                'includes_category_spotlight' => false,
            ],
            [
                'name' => 'Standard Monthly',
                'name_ar' => 'معياري شهري',
                'duration_months' => 1,
                'price_lyd' => 75.00,
                'is_active' => true,
                'tier' => 'standard',
                'featured_days_per_subscription' => 7,
                'includes_homepage_featured' => false,
                'includes_top_search' => false,
                'includes_category_spotlight' => false,
            ],
            [
                'name' => 'Premium Monthly',
                'name_ar' => 'ممتاز شهري',
                'duration_months' => 1,
                'price_lyd' => 150.00,
                'is_active' => true,
                'tier' => 'premium',
                'featured_days_per_subscription' => 15,
                'includes_homepage_featured' => true,
                'includes_top_search' => true,
                'includes_category_spotlight' => true,
            ],
            [
                'name' => 'Basic Yearly',
                'name_ar' => 'أساسي سنوي',
                'duration_months' => 12,
                'price_lyd' => 500.00,
                'is_active' => true,
                'tier' => 'basic',
                'featured_days_per_subscription' => 0,
                'includes_homepage_featured' => false,
                'includes_top_search' => false,
                'includes_category_spotlight' => false,
            ],
            [
                'name' => 'Standard Yearly',
                'name_ar' => 'معياري سنوي',
                'duration_months' => 12,
                'price_lyd' => 700.00,
                'is_active' => true,
                'tier' => 'standard',
                'featured_days_per_subscription' => 60,
                'includes_homepage_featured' => false,
                'includes_top_search' => false,
                'includes_category_spotlight' => false,
            ],
            [
                'name' => 'Premium Yearly',
                'name_ar' => 'ممتاز سنوي',
                'duration_months' => 12,
                'price_lyd' => 1500.00,
                'is_active' => true,
                'tier' => 'premium',
                'featured_days_per_subscription' => 180,
                'includes_homepage_featured' => true,
                'includes_top_search' => true,
                'includes_category_spotlight' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate([
                'name' => $plan['name'],
            ], $plan);
        }
    }
}
