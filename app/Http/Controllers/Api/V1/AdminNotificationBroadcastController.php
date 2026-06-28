<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Admin\BroadcastNotificationRequest;
use App\Jobs\BroadcastAppNotificationJob;
use Illuminate\Http\JsonResponse;

class AdminNotificationBroadcastController extends BaseApiController
{
    public function store(BroadcastNotificationRequest $request): JsonResponse
    {
        BroadcastAppNotificationJob::dispatch($request->validated(), $request->user()?->id)->afterCommit();

        return $this->success([], 'تمت جدولة الإشعارات للإرسال.');
    }
}
