<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->sentence(),
            'user_id' => User::factory(),
            'profile_id' => Profile::factory(),
            'status' => ReviewStatus::APPROVED,
        ];
    }
}
