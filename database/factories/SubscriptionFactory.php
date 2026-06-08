<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => SubscriptionPlan::factory(),
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
            'payment_method' => 'bank_transfer',
            'payment_reference' => $this->faker->uuid(),
            'payment_date' => now()->toDateString(),
            'notes' => $this->faker->sentence(),
            'processed_by' => User::factory(),
            'processed_at' => now(),
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ];
    }
}
