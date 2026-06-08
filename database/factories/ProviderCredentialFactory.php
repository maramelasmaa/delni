<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\ProviderCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderCredential>
 */
class ProviderCredentialFactory extends Factory
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
            'issuer' => $this->faker->company(),
            'issue_date' => $this->faker->dateTime(),
            'verification_url' => $this->faker->url(),
            'notes' => $this->faker->paragraph(),
        ];
    }
}
