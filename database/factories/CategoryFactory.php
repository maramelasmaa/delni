<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'name_ar' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
