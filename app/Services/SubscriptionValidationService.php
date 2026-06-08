<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionValidationService
{
    public function validateOwnership(User $user): void
    {
        if (! $user->hasRole('provider')) {
            throw ValidationException::withMessages([
                'user_id' => __('messages.subscription_providers_only'),
            ]);
        }
    }

    public function validateDates(
        User $provider,
        Subscription $subscription
    ): void {
        if ($subscription->ends_at <= $subscription->starts_at) {
            throw ValidationException::withMessages([
                'ends_at' => __('messages.subscription_end_after_start'),
            ]);
        }

        DB::transaction(function () use ($provider, $subscription) {
            $this->lockProviderAndRejectOverlap(
                provider: $provider,
                startsAt: Carbon::parse($subscription->starts_at),
                endsAt: Carbon::parse($subscription->ends_at),
                exceptSubscriptionId: $subscription->getKey(),
            );
        });
    }

    /**
     * Create a subscription while holding a provider-row lock until insert commit.
     *
     * This is the race-safe path for admin-managed subscription creation. The
     * provider row lock serializes all subscription creates for the same provider,
     * so concurrent admins cannot both pass the overlap check and insert.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createForProvider(User $provider, array $attributes): Subscription
    {
        return DB::transaction(function () use ($provider, $attributes): Subscription {
            $this->validateOwnership($provider);

            $startsAt = Carbon::parse($attributes['starts_at'])->startOfDay();
            $endsAt = Carbon::parse($attributes['ends_at'])->startOfDay();

            if ($endsAt->lessThanOrEqualTo($startsAt)) {
                throw ValidationException::withMessages([
                    'ends_at' => __('messages.subscription_end_after_start'),
                ]);
            }

            $this->lockProviderAndRejectOverlap(
                provider: $provider,
                startsAt: $startsAt,
                endsAt: $endsAt,
            );

            return Subscription::query()->create(array_merge($attributes, [
                'user_id' => $provider->id,
                'starts_at' => $startsAt->toDateString(),
                'ends_at' => $endsAt->toDateString(),
                'is_active' => true,
                'approved_at' => $attributes['approved_at'] ?? now(),
                'approved_by' => $attributes['approved_by'] ?? auth()->id(),
                'processed_at' => $attributes['processed_at'] ?? now(),
                'processed_by' => $attributes['processed_by'] ?? auth()->id(),
            ]));
        }, attempts: 5);
    }

    private function lockProviderAndRejectOverlap(
        User $provider,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int $exceptSubscriptionId = null,
    ): void {
        User::query()
            ->whereKey($provider->id)
            ->lockForUpdate()
            ->firstOrFail();

        $overlap = Subscription::query()
            ->where('user_id', $provider->id)
            ->when($exceptSubscriptionId !== null, fn ($query) => $query->whereKeyNot($exceptSubscriptionId))
            ->whereDate('starts_at', '<=', $endsAt->toDateString())
            ->whereDate('ends_at', '>=', $startsAt->toDateString())
            ->lockForUpdate()
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'starts_at' => __('messages.subscription_dates_overlap'),
            ]);
        }
    }
}
