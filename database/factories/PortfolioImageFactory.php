<?php

namespace Database\Factories;

use App\Models\PortfolioImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioImage>
 */
class PortfolioImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'portfolio_item_id' => null,
            'path' => 'portfolio/'.fake()->uuid().'.jpg',
            'alt' => fake()->sentence(3),
            'sort_order' => 0,
        ];
    }
}
