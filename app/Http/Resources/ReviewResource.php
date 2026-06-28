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
        $isAdmin = $currentUser?->isAdmin() ?? false;
        $isReviewOwner = $currentUser?->id === $this->user_id;
        $isFlagger = $currentUser?->id === $this->flagged_by;
        $canViewModeration = $isAdmin || $isReviewOwner || $isFlagger;
        $canViewFlagDetails = $isAdmin || $isFlagger;

        $flagResponse = null;

        if ($canViewFlagDetails && $this->flagged_by !== null) {
            $flagResponse = $this->flag_handled_at === null
                ? 'pending'
                : ($this->is_flagged ? 'accepted' : 'rejected');
        }

        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'user_name' => $this->user->name ?? 'عميل',
            'status' => $this->when(
                $canViewModeration,
                $this->status instanceof \UnitEnum ? $this->status->value : $this->status,
            ),
            'moderation_note' => $this->when($canViewModeration, $this->moderation_note),
            'flagged_reason' => $this->when($canViewFlagDetails, $this->flagged_reason),
            'flag_response' => $this->when($canViewFlagDetails, $flagResponse),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
