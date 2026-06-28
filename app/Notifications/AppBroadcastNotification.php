<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class AppBroadcastNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly array $payload,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $data = is_array($this->payload['data'] ?? null) ? $this->payload['data'] : [];

        return new DatabaseMessage([
            'type' => 'app_broadcast',
            'title' => $this->payload['title'],
            'body' => $this->payload['body'],
            'data' => $data,
            'url' => $data['url'] ?? null,
            'pathname' => $data['pathname'] ?? null,
            'provider_slug' => $data['provider_slug'] ?? null,
            'category_slug' => $data['category_slug'] ?? null,
            'subcategory_slug' => $data['subcategory_slug'] ?? null,
            'created_at' => now()->toIso8601String(),
        ]);
    }
}
