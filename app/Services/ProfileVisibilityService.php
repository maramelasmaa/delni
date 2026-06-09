<?php

namespace App\Services;

use App\Data\ProfileVisibilityResult;
use App\Enums\ProfileHiddenReason;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProfileVisibilityService
{
    /**
     * Evaluate full visibility state of a profile.
     *
     * Returns structured result with:
     * - is_visible: boolean
     * - primary_reason: why hidden (if not visible)
     * - description: human-readable explanation
     * - missing_fields: which profile fields incomplete
     * - subscription_status: active/expired/missing
     *
     * Rule: Profile is discoverable if ALL are true:
     * 1. Profile belongs to a user
     * 2. User is active
     * 3. User is not suspended
     * 4. Profile is complete
     * 5. User has an active subscription with future end_date
     */
    public function evaluate(Profile $profile): ProfileVisibilityResult
    {
        $user = $profile->user;

        // Check: User exists
        if (! $user) {
            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::NO_USER,
                user_status: 'not_found',
            );
        }

        // Check: User is active
        if (! $user->is_active) {
            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::USER_INACTIVE,
                user_status: 'inactive',
            );
        }

        // Check: User is not suspended
        if ($user->is_suspended) {
            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::USER_SUSPENDED,
                user_status: 'suspended',
            );
        }

        // Check: Profile is complete
        if (! $profile->is_complete) {
            $missingFields = $this->getMissingRequiredFields($profile);

            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::PROFILE_INCOMPLETE,
                missing_fields: $missingFields,
                user_status: 'active',
            );
        }

        // Check: Has active, non-expired subscription
        $subscription = $user->subscriptions()
            ->where('is_active', true)
            ->whereDate('ends_at', '>=', Carbon::today())
            ->first();

        if (! $subscription) {
            // Distinguish: no subscription vs expired subscription
            $anySubscription = $user->subscriptions()->first();

            if (! $anySubscription) {
                return new ProfileVisibilityResult(
                    is_visible: false,
                    primary_reason: ProfileHiddenReason::NO_ACTIVE_SUBSCRIPTION,
                    subscription_status: 'none',
                    user_status: 'active',
                );
            }

            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::SUBSCRIPTION_EXPIRED,
                subscription_status: 'expired',
                subscription_expires_at: $anySubscription->ends_at->toDateString(),
                user_status: 'active',
            );
        }

        // All checks passed
        return new ProfileVisibilityResult(
            is_visible: true,
            subscription_status: 'active',
            subscription_expires_at: $subscription->ends_at->toDateString(),
            user_status: 'active',
        );
    }

    /**
     * Check if a single profile is discoverable (visible in search/public).
     *
     * Convenience method for boolean checks. Use evaluate() for diagnostics.
     */
    public function isDiscoverable(Profile $profile): bool
    {
        return $this->evaluate($profile)->is_visible;
    }

    /**
     * Get list of required fields that are missing from a profile.
     *
     * @return array<string>
     */
    private function getMissingRequiredFields(Profile $profile): array
    {
        $missing = [];

        // Check required fields from completeness calculation
        if (! filled($profile->business_name) && ! filled($profile->user?->name)) {
            $missing[] = 'business_name';
        }

        if (! $profile->city_id) {
            $missing[] = 'city';
        }

        if (! $profile->category_id) {
            $missing[] = 'category';
        }

        if (! filled($profile->phone)) {
            $missing[] = 'phone';
        }

        if (! filled($profile->whatsapp)) {
            $missing[] = 'whatsapp';
        }

        return $missing;
    }

    /**
     * Apply visibility conditions to a Profile query builder.
     *
     * This is the single source of truth for visibility logic.
     * Used by search, homepage, category, city, subcategory pages.
     *
     * Conditions applied:
     * - User exists and is not soft-deleted
     * - User is active
     * - User is not suspended
     * - Profile is complete
     * - User has active subscription with future end_date
     *
     * @param  Builder<Profile>  $query
     * @return Builder<Profile>
     */
    public function applyVisibleQuery(Builder $query): Builder
    {
        return $query
            ->whereNull('users.deleted_at')
            ->where('users.is_active', true)
            ->where('users.is_suspended', false)
            ->where('profiles.is_complete', true)
            ->whereExists(function ($sub): void {
                $sub->select(DB::raw(1))
                    ->from('subscriptions')
                    ->whereColumn('subscriptions.user_id', 'profiles.user_id')
                    ->where('subscriptions.is_active', true)
                    ->whereDate('subscriptions.ends_at', '>=', Carbon::today());
            });
    }
}
