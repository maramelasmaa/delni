<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\ProviderLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderLink>
 */
class ProviderLinkFactory extends Factory
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
            'type' => $this->faker->randomElement(['website', 'linkedin', 'github', 'instagram', 'twitter', 'facebook', 'other']),
            'label' => $this->faker->words(2, true),
            'url' => $this->faker->url(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
