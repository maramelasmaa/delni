<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        $tiers = ['basic', 'standard', 'premium'];

        return [
            'name' => $this->faker->word().' Plan',
            'name_ar' => $this->faker->word().' خطة',
            'duration_months' => $this->faker->randomElement([1, 3, 6, 12]),
            'price_lyd' => $this->faker->numberBetween(50, 1000),
            'is_active' => true,
            'tier' => $this->faker->randomElement($tiers),
            'featured_days_per_subscription' => $this->faker->numberBetween(0, 180),
            'includes_homepage_featured' => $this->faker->boolean(),
            'includes_top_search' => $this->faker->boolean(),
            'includes_category_spotlight' => $this->faker->boolean(),
        ];
    }
}
