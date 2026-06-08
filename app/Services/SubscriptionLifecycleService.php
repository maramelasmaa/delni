<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SubscriptionLifecycleService
{
    public function __construct(
        private SubscriptionValidationService $validation,
    ) {}

    /**
     * Prepare a subscription for creation by validating and stamping required fields.
     * This is called by SubscriptionObserver::creating hook to ensure business logic
     * is not bypassed by Subscription::withoutEvents() or raw DB inserts.
     */
    public function prepareForCreation(Subscription $subscription): void
    {
        $this->validation->validateOwnership($subscription->user);
        $this->validation->validateDates($subscription->user, $subscription);

        $subscription->is_active = true;
        $subscription->approved_at = now();
        $subscription->approved_by = Auth::id();
        $subscription->processed_at = now();
        $subscription->processed_by = Auth::id();
    }

    /**
     * Assert that immutable subscription fields have not been changed.
     * Throws ValidationException if user_id, plan_id, starts_at, or ends_at are dirty.
     * This is called by SubscriptionObserver::updating hook.
     */
    public function assertImmutableFieldsUnchanged(Subscription $subscription): void
    {
        $immutableFields = ['user_id', 'plan_id', 'starts_at', 'ends_at'];

        foreach ($immutableFields as $field) {
            if ($subscription->isDirty($field)) {
                throw ValidationException::withMessages([
                    $field => __('This field cannot be changed once set.'),
                ]);
            }
        }
    }
}
