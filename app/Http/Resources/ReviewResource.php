<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUser = $request->user();
        $isOwnerOrAdmin = $currentUser && ($currentUser->id === $this->user_id || $currentUser->isAdmin());

        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'user_name' => $this->user->name ?? 'عميل',
            'status' => $this->when($isOwnerOrAdmin, $this->status instanceof \UnitEnum ? $this->status->value : $this->status),
            'moderation_note' => $this->when($isOwnerOrAdmin, $this->moderation_note),
            'flag_response' => $this->when(
                $isOwnerOrAdmin && $this->flag_handled_at !== null,
                $this->is_flagged ? 'accepted' : 'rejected'
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
