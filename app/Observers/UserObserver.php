<?php

namespace App\Observers;

use App\Jobs\SoftDeleteUserProfileJob;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\ProfileCompletenessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;

class UserObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly ProfileCompletenessService $completeness,
    ) {}

    public function created(User $user): void
    {
        // NOTE: Profile creation is now synchronous in CreateUser page (ProviderCreationService).
        // The async job was removed to ensure provider creation doesn't depend on queue workers.
        // See: ProviderCreationService for critical business logic.

        $this->activityLog->log(
            actorId: Context::get('actor_id') ?? Auth::id(),
            subject: $user,
            action: 'user_created',
            description: "User account created: {$user->email}",
            properties: [],
        );
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('is_suspended')) {
            $this->logSuspensionChange($user);
            $this->clearPublicCacheOnSuspension();
        }

        if ($user->wasChanged('password')) {
            $this->activityLog->log(
                actorId: Context::get('actor_id') ?? Auth::id(),
                subject: $user,
                action: 'password_changed',
                description: "Password changed for user: {$user->email}",
                properties: [],
            );
        }

        if ($user->wasChanged('name')) {
            if ($profile = $user->profile) {
                $this->completeness->evaluate($profile);
            }
        }
    }

    public function deleted(User $user): void
    {
        // Synchronously soft-delete the profile so correctness doesn't depend on queue.
        // The job below is a redundant safety net for partial failure recovery.
        $user->profile?->delete();

        SoftDeleteUserProfileJob::dispatch($user->id)->afterCommit();

        $this->clearPublicCacheOnSuspension();

        $this->activityLog->log(
            actorId: Context::get('actor_id') ?? Auth::id(),
            subject: $user,
            action: 'user_deleted',
            description: "User account soft-deleted: {$user->email}",
            properties: [],
        );
    }

    private function logSuspensionChange(User $user): void
    {
        if ($user->is_suspended) {
            $this->activityLog->log(
                actorId: Context::get('actor_id') ?? Auth::id(),
                subject: $user,
                action: 'user_suspended',
                description: "User suspended: {$user->email}",
                properties: ['reason' => $user->suspension_reason],
            );
        } else {
            $this->activityLog->log(
                actorId: Context::get('actor_id') ?? Auth::id(),
                subject: $user,
                action: 'user_unsuspended',
                description: "User reinstated: {$user->email}",
                properties: ['reason' => $user->reinstatement_reason],
            );
        }
    }

    private function clearPublicCacheOnSuspension(): void
    {
        // Keys must match the '.global' suffix written by PublicFrontendService::profileCountByColumn()
        Cache::forget('frontend.profile_counts.profiles_category_id.global');
        Cache::forget('frontend.profile_counts.profiles_city_id.global');
        Cache::forget('frontend.profile_counts.subcategory_id.global');
        // Also clear the flexible cache stale-marker entries
        Cache::forget('illuminate:cache:flexible:created:frontend.profile_counts.profiles_category_id.global');
        Cache::forget('illuminate:cache:flexible:created:frontend.profile_counts.profiles_city_id.global');
        Cache::forget('illuminate:cache:flexible:created:frontend.profile_counts.subcategory_id.global');
    }
}
