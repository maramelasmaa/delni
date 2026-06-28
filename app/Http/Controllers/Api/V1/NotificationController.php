<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 5), 50);

        $paginator = $request->user()
            ->notifications()
            ->latest()
            ->paginate($perPage);

        return $this->paginated($paginator, NotificationResource::class);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return $this->success([
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $record = $this->findOwnedNotification($request, $notification);

        if ($record->read_at === null) {
            $record->markAsRead();
        }

        return $this->success(new NotificationResource($record->fresh()), 'تم تحديث الإشعار بنجاح.');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return $this->success([], 'تم تعليم جميع الإشعارات كمقروءة.');
    }

    private function findOwnedNotification(Request $request, string $notificationId): DatabaseNotification
    {
        return $request->user()
            ->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();
    }
}
