<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('user', 'web');
    }

    private function makeUser(string $password = 'Secret123'): User
    {
        $user = User::factory()->create([
            'password' => Hash::make($password),
            'is_active' => true,
        ]);
        $user->assignRole('user');

        return $user;
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('current-device')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'Secret123',
                'password' => 'NewSecret456',
                'password_confirmation' => 'NewSecret456',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertTrue(Hash::check('NewSecret456', $user->fresh()->password));
    }

    public function test_change_password_revokes_other_tokens_but_keeps_current(): void
    {
        $user = $this->makeUser();
        $currentPlain = $user->createToken('current-device')->plainTextToken;
        $otherTokenId = $user->createToken('other-device')->accessToken->getKey();
        $currentTokenId = (int) explode('|', $currentPlain, 2)[0];

        $this->withHeader('Authorization', 'Bearer '.$currentPlain)
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'Secret123',
                'password' => 'NewSecret456',
                'password_confirmation' => 'NewSecret456',
            ])
            ->assertOk();

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $currentTokenId]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $otherTokenId]);
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('d')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'WrongPass123',
                'password' => 'NewSecret456',
                'password_confirmation' => 'NewSecret456',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('Secret123', $user->fresh()->password));
    }

    public function test_change_password_rejects_weak_new_password(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('d')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'Secret123',
                'password' => 'weak',
                'password_confirmation' => 'weak',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_change_password_rejects_new_password_equal_to_current(): void
    {
        $user = $this->makeUser();
        $token = $user->createToken('d')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'Secret123',
                'password' => 'Secret123',
                'password_confirmation' => 'Secret123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_change_password_requires_authentication(): void
    {
        $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'Secret123',
            'password' => 'NewSecret456',
            'password_confirmation' => 'NewSecret456',
        ])->assertUnauthorized();
    }
}
