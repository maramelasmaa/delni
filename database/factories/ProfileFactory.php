<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        $slug = 'profile-'.$this->faker->unique()->slug();

        return [
            'user_id' => User::factory(),
            'business_name' => $this->faker->company(),
            'type' => 'business',
            'provider_type' => 'company',
            'bio' => $this->faker->paragraph(),
            'slug' => $slug,
            'offers_remote_work' => $this->faker->boolean(),
            'map_url' => $this->faker->url(),
            'service_area_note' => $this->faker->sentence(),
            'city_id' => City::factory(),
            'category_id' => Category::factory(),
            'whatsapp' => '+2189'.$this->faker->numerify('#########'),
            'phone' => '+2189'.$this->faker->numerify('#########'),
            'experience_years' => $this->faker->numberBetween(1, 50),
            'is_complete' => false,
            'website' => $this->faker->url(),
            'instagram' => '@'.$this->faker->userName(),
            'facebook' => 'https://facebook.com/'.$this->faker->userName(),
            'linkedin' => 'https://linkedin.com/in/'.$this->faker->userName(),
        ];
    }

    public function complete(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_complete' => true,
            ];
        });
    }

    public function withStats(): static
    {
        return $this->afterCreating(function (Profile $profile): void {
            // Only create stats if they don't already exist
            if (! \App\Models\ProfileStats::where('profile_id', $profile->id)->exists()) {
                \App\Models\ProfileStats::create([
                    'profile_id' => $profile->id,
                    'rating_avg' => 0.0,
                    'reviews_count' => 0,
                    'is_top_rated' => false,
                    'is_homepage_featured' => false,
                    'homepage_featured_until' => null,
                    'is_top_search' => false,
                    'top_search_until' => null,
                    'is_top_category' => false,
                    'top_category_until' => null,
                    'is_top_subcategory' => false,
                    'top_subcategory_until' => null,
                ]);
            }
        });
    }
}
