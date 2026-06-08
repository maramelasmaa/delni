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
        public readonly ?string $subscription_status = null,
        public readonly ?string $subscription_expires_at = null,
        public readonly ?string $user_status = null,
        public readonly array $missing_fields = [],
    ) {}

    /**
     * Human-readable description of why profile is hidden.
     */
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
            ProfileHiddenReason::NO_ACTIVE_SUBSCRIPTION => 'No active subscription',
            ProfileHiddenReason::SUBSCRIPTION_EXPIRED => 'Subscription expired on '.$this->subscription_expires_at,
            default => 'Profile is hidden',
        };
    }

    /**
     * Is this profile hidden due to completeness issues?
     */
    public function isIncomplete(): bool
    {
        return $this->primary_reason === ProfileHiddenReason::PROFILE_INCOMPLETE;
    }

    /**
     * Is this profile hidden due to subscription issues?
     */
    public function isSubscriptionIssue(): bool
    {
        return in_array(
            $this->primary_reason,
            [ProfileHiddenReason::NO_ACTIVE_SUBSCRIPTION, ProfileHiddenReason::SUBSCRIPTION_EXPIRED],
            true
        );
    }

    /**
     * Is this profile hidden due to user account issues?
     */
    public function isUserAccountIssue(): bool
    {
        return in_array(
            $this->primary_reason,
            [ProfileHiddenReason::USER_INACTIVE, ProfileHiddenReason::USER_SUSPENDED, ProfileHiddenReason::NO_USER],
            true
        );
    }

    /**
     * Array representation for JSON responses.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'is_visible' => $this->is_visible,
            'primary_reason' => $this->primary_reason?->value,
            'description' => $this->getDescription(),
            'user_status' => $this->user_status,
            'subscription_status' => $this->subscription_status,
            'subscription_expires_at' => $this->subscription_expires_at,
            'missing_fields' => $this->missing_fields,
        ];
    }
}
