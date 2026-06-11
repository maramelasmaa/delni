<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ChatRateLimiter
{
    /**
     * @return array{allowed: bool, reason: string|null}
     */
    public function check(?User $user, string $ipAddress): array
    {
        $identity = $user?->id !== null ? 'user:'.$user->id : 'guest:'.$ipAddress;

        if (! $this->hit('chat:burst:'.$identity, 3, 60)) {
            return ['allowed' => false, 'reason' => 'burst'];
        }

        if ($user !== null && ! $this->hit('chat:user:'.$user->id, 50, 86400)) {
            return ['allowed' => false, 'reason' => 'daily'];
        }

        if ($user === null && ! $this->hit('chat:guest:'.$ipAddress, 10, 3600)) {
            return ['allowed' => false, 'reason' => 'hourly'];
        }

        return ['allowed' => true, 'reason' => null];
    }

    private function hit(string $key, int $maxAttempts, int $seconds): bool
    {
        Cache::add($key, 0, $seconds);

        return Cache::increment($key) <= $maxAttempts;
    }
}
