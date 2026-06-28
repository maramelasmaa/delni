<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Support\Arr;

class PushTokenService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function register(User $user, array $attributes): PushToken
    {
        $payload = [
            'user_id' => $user->id,
            'provider' => (string) Arr::get($attributes, 'provider', 'expo'),
            'platform' => (string) $attributes['platform'],
            'device_name' => Arr::get($attributes, 'device_name'),
            'is_active' => (bool) Arr::get($attributes, 'is_active', true),
            'last_seen_at' => now(),
        ];

        /** @var PushToken $pushToken */
        $pushToken = PushToken::query()->updateOrCreate(
            ['token' => (string) $attributes['token']],
            $payload,
        );

        return $pushToken->fresh();
    }

    /**
     * @param  array<int, string>  $tokens
     */
    public function deactivateTokens(array $tokens): void
    {
        if ($tokens === []) {
            return;
        }

        PushToken::query()
            ->whereIn('token', $tokens)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }
}
