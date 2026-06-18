<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subcategory>
 */
class SubcategoryFactory extends Factory
{
    protected $model = Subcategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameAr = $this->faker->word();

        return [
            'category_id' => Category::factory(),
            'name' => $this->faker->word(),
            'name_ar' => $nameAr,
            'search_name' => $nameAr,
            'slug' => $this->faker->slug(),
            'is_active' => true,
        ];
    }
}
