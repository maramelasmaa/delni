<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Admin\BroadcastNotificationRequest;
use App\Jobs\BroadcastAppNotificationJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminNotificationBroadcastController extends BaseApiController
{
    public function store(BroadcastNotificationRequest $request): JsonResponse
    {
        // Explicitly start a database transaction to ensure afterCommit() executes
        DB::transaction(function () use ($request): void {
            BroadcastAppNotificationJob::dispatch(
                $request->validated(),
                $request->user()?->id
            )->afterCommit();
        });

        Log::info('BroadcastAppNotificationJob dispatched', [
            'triggered_by_user_id' => $request->user()?->id,
            'payload_keys' => array_keys($request->validated()),
        ]);

        return $this->success([], 'تمت جدولة الإشعارات للإرسال.');
    }
}
