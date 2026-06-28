<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PushTokenApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_register_push_token(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/push-tokens', [
                'token' => 'ExponentPushToken[abc123XYZ]',
                'provider' => 'expo',
                'platform' => 'android',
                'device_name' => 'Pixel 9',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token', 'ExponentPushToken[abc123XYZ]')
            ->assertJsonPath('data.is_active', true);

        $token = PushToken::query()->first();

        $this->assertNotNull($token);
        $this->assertSame($user->id, $token->user_id);
        $this->assertSame('expo', $token->provider);
        $this->assertSame('android', $token->platform);
        $this->assertTrue($token->is_active);
        $this->assertNotNull($token->last_seen_at);
    }

    public function test_duplicate_push_token_is_upserted_without_creating_duplicates(): void
    {
        $firstUser = User::factory()->create();
        $firstUser->assignRole('user');

        $secondUser = User::factory()->create();
        $secondUser->assignRole('user');

        PushToken::query()->create([
            'user_id' => $firstUser->id,
            'token' => 'ExponentPushToken[dupToken123]',
            'provider' => 'expo',
            'platform' => 'ios',
            'device_name' => 'iPhone 15',
            'is_active' => false,
            'last_seen_at' => now()->subDay(),
        ]);

        $this->actingAs($secondUser, 'sanctum')
            ->postJson('/api/v1/auth/push-tokens', [
                'token' => 'ExponentPushToken[dupToken123]',
                'provider' => 'expo',
                'platform' => 'android',
                'device_name' => 'Samsung S24',
            ])
            ->assertOk()
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.platform', 'android');

        $this->assertDatabaseCount('push_tokens', 1);
        $this->assertDatabaseHas('push_tokens', [
            'token' => 'ExponentPushToken[dupToken123]',
            'user_id' => $secondUser->id,
            'platform' => 'android',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_register_push_token(): void
    {
        $this->postJson('/api/v1/auth/push-tokens', [
            'token' => 'ExponentPushToken[guestToken123]',
            'provider' => 'expo',
            'platform' => 'ios',
        ])->assertUnauthorized();
    }
}
