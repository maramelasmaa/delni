<?php

namespace App\Observers;

use App\Models\Subscription;
use App\Services\ActivityLogService;
use App\Services\SubscriptionLifecycleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

class SubscriptionObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly SubscriptionLifecycleService $lifecycle,
    ) {}

    /**
     * Delegate subscription creation validation and stamping to SubscriptionLifecycleService.
     * This ensures business correctness is not bypassed by Subscription::withoutEvents()
     * or direct DB inserts — the service can be called explicitly from any creation path.
     */
    public function creating(Subscription $subscription): void
    {
        $this->lifecycle->prepareForCreation($subscription);
    }

    /**
     * Delegate immutability checks to SubscriptionLifecycleService.
     */
    public function updating(Subscription $subscription): void
    {
        $this->lifecycle->assertImmutableFieldsUnchanged($subscription);
    }

    public function created(Subscription $subscription): void
    {
        $this->activityLog->log(
            actorId: Context::get('actor_id') ?? Auth::id(),
            subject: $subscription,
            action: 'subscription_created',
            description: "Subscription created for provider #{$subscription->user_id}",
            properties: [
                'plan_id' => $subscription->plan_id,
                'starts_at' => $subscription->starts_at,
                'ends_at' => $subscription->ends_at,
                'is_active' => $subscription->is_active,
            ],
        );
    }

    public function updated(Subscription $subscription): void
    {
        // Only log significant changes (deactivation or date extensions).
        if ($subscription->wasChanged(['is_active', 'ends_at', 'notes'])) {
            $this->activityLog->log(
                actorId: Context::get('actor_id') ?? Auth::id(),
                subject: $subscription,
                action: $subscription->is_active ? 'subscription_modified' : 'subscription_deactivated',
                description: $subscription->is_active
                    ? "Subscription #{$subscription->id} modified for provider #{$subscription->user_id}"
                    : "Subscription #{$subscription->id} deactivated (expired or manually) for provider #{$subscription->user_id}",
                properties: [
                    'is_active' => $subscription->is_active,
                    'ends_at' => $subscription->ends_at,
                ],
            );
        }
    }
}
