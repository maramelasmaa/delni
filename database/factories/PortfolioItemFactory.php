<?php

namespace Database\Factories;

use App\Models\PortfolioItem;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioItem>
 */
class PortfolioItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'title' => $this->faker->words(3, true),
            'short_description' => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(),
            'main_url' => $this->faker->url(),
            'link' => $this->faker->url(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
