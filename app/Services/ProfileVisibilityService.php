<?php

namespace App\Services;

use App\Data\ProfileVisibilityResult;
use App\Enums\ProfileHiddenReason;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProfileVisibilityService
{
    /**
     * Evaluate full visibility state of a profile.
     *
     * A provider is publicly visible ONLY IF:
     * - user is active and not suspended
     * - profile is complete and not soft-deleted
     * - profiles.provider_access_ends_at IS NOT NULL AND >= now()
     */
    public function evaluate(Profile $profile): ProfileVisibilityResult
    {
        $profile->loadMissing('user');
        $user = $profile->user;

        if (! $user) {
            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::NO_USER,
                user_status: 'not_found',
            );
        }

        if (! $user->isProvider()) {
            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::NOT_PROVIDER,
                user_status: 'active',
            );
        }

        if (! $user->is_active) {
            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::USER_INACTIVE,
                user_status: 'inactive',
            );
        }

        if ($user->is_suspended) {
            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::USER_SUSPENDED,
                user_status: 'suspended',
            );
        }

        if (! $profile->is_complete) {
            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::PROFILE_INCOMPLETE,
                missing_fields: $this->getMissingRequiredFields($profile),
                user_status: 'active',
            );
        }

        if (
            ! $profile->provider_access_ends_at
            || $profile->provider_access_ends_at->isPast()
        ) {
            return new ProfileVisibilityResult(
                is_visible: false,
                primary_reason: ProfileHiddenReason::ACCESS_EXPIRED,
                user_status: 'active',
                access_ends_at: $profile->provider_access_ends_at?->toDateTimeString(),
            );
        }

        return new ProfileVisibilityResult(
            is_visible: true,
            user_status: 'active',
            access_ends_at: $profile->provider_access_ends_at->toDateTimeString(),
        );
    }

    public function isDiscoverable(Profile $profile): bool
    {
        return $this->evaluate($profile)->is_visible;
    }

    /**
     * @return array<string>
     */
    private function getMissingRequiredFields(Profile $profile): array
    {
        $missing = [];
        $profile->loadMissing('user');

        if (! filled($profile->business_name) && ! filled($profile->user?->name)) {
            $missing[] = 'business_name';
        }
        if (! $profile->city_id) {
            $missing[] = 'city';
        }
        if (! $profile->category_id) {
            $missing[] = 'category';
        }
        if (! $profile->subcategories()->exists()) {
            $missing[] = 'subcategories';
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
            ->whereNotNull('profiles.provider_access_ends_at')
            ->where('profiles.provider_access_ends_at', '>=', Carbon::now())
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->whereColumn('model_has_roles.model_id', 'users.id')
                    ->where('model_has_roles.model_type', User::class)
                    ->where('roles.name', 'provider');
            });
    }
}
