<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('user', 'web');
    }

    private function actingUser(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('user');

        return $user;
    }

    public function test_user_can_update_name_phone_and_email(): void
    {
        $user = $this->actingUser();
        $token = $user->createToken('d')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/auth/profile', [
                'name' => 'Maram Elasma',
                'phone' => '916640261',
                'email' => 'NEW@Example.com',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Maram Elasma')
            ->assertJsonPath('data.phone', '916640261')
            ->assertJsonPath('data.email', 'new@example.com');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Maram Elasma', 'email' => 'new@example.com']);
    }

    public function test_partial_update_only_changes_supplied_fields(): void
    {
        $user = $this->actingUser();
        $original = $user->email;
        $token = $user->createToken('d')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/auth/profile', ['name' => 'Only Name'])
            ->assertOk();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Only Name', 'email' => $original]);
    }

    public function test_email_must_be_unique(): void
    {
        $taken = User::factory()->create(['email' => 'taken@example.com']);
        $user = $this->actingUser();
        $token = $user->createToken('d')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/auth/profile', ['email' => 'taken@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_requires_authentication(): void
    {
        $this->patchJson('/api/v1/auth/profile', ['name' => 'X'])->assertUnauthorized();
    }
}
