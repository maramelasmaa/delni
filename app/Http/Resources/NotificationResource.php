<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : [];

        return [
            'id' => $this->id,
            'type' => $data['type'] ?? class_basename($this->type),
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'data' => is_array($data['data'] ?? null) ? $data['data'] : null,
            'decision' => $data['decision'] ?? null,
            'reason' => $data['reason'] ?? null,
            'review_id' => $data['review_id'] ?? null,
            'profile_id' => $data['profile_id'] ?? null,
            'profile_slug' => $data['profile_slug'] ?? null,
            'url' => $data['url'] ?? null,
            'pathname' => $data['pathname'] ?? null,
            'provider_slug' => $data['provider_slug'] ?? null,
            'category_slug' => $data['category_slug'] ?? null,
            'subcategory_slug' => $data['subcategory_slug'] ?? null,
            'flagged_reason' => $data['flagged_reason'] ?? null,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
