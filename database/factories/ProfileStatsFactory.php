<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\ProfileStats;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileStatsFactory extends Factory
{
    protected $model = ProfileStats::class;

    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'rating_avg' => $this->faker->numberBetween(1, 5),
            'reviews_count' => $this->faker->numberBetween(0, 50),
            'is_top_rated' => false,
            'is_featured' => false,
            'featured_until' => null,
            'is_homepage_featured' => false,
            'homepage_featured_until' => null,
            'is_top_search' => false,
            'top_search_until' => null,
            'is_top_category' => false,
            'top_category_until' => null,
            'is_top_subcategory' => false,
            'top_subcategory_until' => null,
        ];
    }
}
