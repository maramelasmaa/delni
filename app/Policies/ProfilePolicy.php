<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;
use App\Services\ProfileVisibilityService;

class ProfilePolicy
{
    public function __construct(
        private ProfileVisibilityService $visibility,
    ) {}

    /**
     * Admin bypasses most profile checks.
     *
     * 'create' and 'delete' are EXCLUDED from bypass — documented pattern.
     * Profiles are never manually created (P1) and never directly deleted.
     * Even admin must not be able to bypass these via a route.
     *
     * Documented: https://laravel.com/docs/11.x/authorization#policy-filters
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')
            && ! in_array($ability, ['create', 'delete'])
        ) {
            return true;
        }

        return null;
    }

    /**
     * Anyone can hit the profile listing/browse page.
     * Visibility filtering is handled by the query layer — SD1.
     * Guest access via nullable ?User.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Anyone including guests can view a publicly visible provider profile.
     * Owner can always view their own profile regardless of visibility.
     *
     * Invariants: P4, P6, SD1.
     */
    public function view(?User $user, Profile $profile): bool
    {
        if ($user && $profile->user_id === $user->id) {
            return true;
        }

        return $this->visibility->isDiscoverable($profile);
    }

    /**
     * Only the profile owner with provider role can update their own profile.
     * Admin handled by before().
     * Invariant: provider edits own profile only.
     */
    public function update(User $user, Profile $profile): bool
    {
        return $profile->user_id === $user->id
            && $user->hasRole('provider');
    }

    /**
     * Profiles are never created manually — auto-created by system only.
     * Excluded from before() bypass — even admin is denied here.
     * Any route calling authorize('create', Profile::class) is always denied.
     * Invariant: P1.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Profiles are only soft-deleted via user deletion cascade — never directly.
     * Excluded from before() bypass — even admin is denied here.
     * Profile deletion happens through user deletion, not a profile delete route.
     */
    public function delete(User $user, Profile $profile): bool
    {
        return false;
    }
}
