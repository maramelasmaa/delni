<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    /**
     * Admin bypasses most subscription checks.
     *
     * 'delete' is EXCLUDED from bypass.
     * Subscriptions are permanent financial records — PAY3.
     * Even admin cannot delete a subscription through any route.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')
            && $ability !== 'delete'
        ) {
            return true;
        }

        return null;
    }

    /**
     * Provider can view their own subscription listing.
     * Admin handled by before().
     * Public users cannot list any subscriptions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('provider');
    }

    /**
     * Provider can view a specific subscription they own.
     * Admin handled by before().
     */
    public function view(User $user, Subscription $subscription): bool
    {
        return $subscription->user_id === $user->id
            && $user->hasRole('provider');
    }

    /**
     * Only admin can create subscriptions.
     * Admin handled by before().
     * Invariants: S1, subscription ownership.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Only admin can update subscriptions.
     * Admin handled by before().
     */
    public function update(User $user, Subscription $subscription): bool
    {
        return false;
    }

    /**
     * Subscriptions are NEVER deleted — permanent financial record.
     * Excluded from before() bypass — even admin is denied here.
     * Invariant: PAY3, history preservation.
     */
    public function delete(User $user, Subscription $subscription): bool
    {
        return false;
    }

    /**
     * Only admin can approve subscriptions.
     * Admin handled by before().
     * Invariants: S5, S6.
     */
    public function approve(User $user, Subscription $subscription): bool
    {
        return false;
    }
}
