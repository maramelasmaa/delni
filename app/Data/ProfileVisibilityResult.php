<?php

namespace App\Data;

use App\Enums\ProfileHiddenReason;

class ProfileVisibilityResult
{
    /**
     * @param  array<ProfileHiddenReason>  $reasons
     * @param  array<string>  $missingFields
     */
    public function __construct(
        public readonly bool $is_visible,
        public readonly ?ProfileHiddenReason $primary_reason = null,
        public readonly array $reasons = [],
        public readonly ?string $user_status = null,
        public readonly array $missing_fields = [],
        public readonly ?string $access_ends_at = null,
    ) {}

    public function getDescription(): string
    {
        if ($this->is_visible) {
            return 'Profile is visible in search and public discovery';
        }

        return match ($this->primary_reason) {
            ProfileHiddenReason::NO_USER => 'User account not found',
            ProfileHiddenReason::USER_INACTIVE => 'User account is inactive',
            ProfileHiddenReason::USER_SUSPENDED => 'User account is suspended',
            ProfileHiddenReason::PROFILE_INCOMPLETE => 'Profile is incomplete (missing: '.implode(', ', $this->missing_fields).')',
            ProfileHiddenReason::ACCESS_EXPIRED => 'Provider listing access has expired or was not granted',
            default => 'Profile is hidden',
        };
    }

    public function isIncomplete(): bool
    {
        return $this->primary_reason === ProfileHiddenReason::PROFILE_INCOMPLETE;
    }

    public function isAccessExpired(): bool
    {
        return $this->primary_reason === ProfileHiddenReason::ACCESS_EXPIRED;
    }

    public function isUserAccountIssue(): bool
    {
        return in_array(
            $this->primary_reason,
            [ProfileHiddenReason::USER_INACTIVE, ProfileHiddenReason::USER_SUSPENDED, ProfileHiddenReason::NO_USER],
            true
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'is_visible' => $this->is_visible,
            'primary_reason' => $this->primary_reason?->value,
            'description' => $this->getDescription(),
            'user_status' => $this->user_status,
            'access_ends_at' => $this->access_ends_at,
            'missing_fields' => $this->missing_fields,
        ];
    }
}
