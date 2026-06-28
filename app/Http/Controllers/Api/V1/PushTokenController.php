<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\RegisterPushTokenRequest;
use App\Services\PushTokenService;
use Illuminate\Http\JsonResponse;

class PushTokenController extends BaseApiController
{
    public function __construct(
        private readonly PushTokenService $pushTokenService,
    ) {}

    public function store(RegisterPushTokenRequest $request): JsonResponse
    {
        $pushToken = $this->pushTokenService->register(
            user: $request->user(),
            attributes: $request->validated(),
        );

        return $this->success([
            'id' => $pushToken->id,
            'token' => $pushToken->token,
            'provider' => $pushToken->provider,
            'platform' => $pushToken->platform,
            'device_name' => $pushToken->device_name,
            'is_active' => $pushToken->is_active,
            'last_seen_at' => $pushToken->last_seen_at?->toIso8601String(),
        ], 'تم تحديث رمز الإشعارات بنجاح.');
    }
}
