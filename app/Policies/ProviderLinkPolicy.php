<?php

namespace App\Policies;

use App\Models\ProviderLink;
use App\Models\User;
use App\Services\ProfileVisibilityService;

class ProviderLinkPolicy
{
    public function __construct(
        private ProfileVisibilityService $visibility,
    ) {}

    /**
     * Admin bypasses all provider link checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Any user or guest can view provider links listing.
     * Actual visibility filtering handled at query level.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Provider links inherit profile visibility.
     * Guests can view links on visible profiles only if link is active.
     * Profile owner always sees their own links.
     */
    public function view(?User $user, ProviderLink $link): bool
    {
        $profile = $link->profile;

        if (! $profile) {
            return false;
        }

        if ($user && $profile->user_id === $user->id) {
            return true;
        }

        return $link->is_active && $this->visibility->isDiscoverable($profile);
    }

    /**
     * Only providers can create links.
     * Provider must own the profile they're creating links for.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('provider') && $user->profile !== null;
    }

    /**
     * Only the profile owner (provider) can update their links.
     */
    public function update(User $user, ProviderLink $link): bool
    {
        return $user->hasRole('provider')
            && $link->profile->user_id === $user->id;
    }

    /**
     * Only the profile owner (provider) can delete their links.
     * Admin can delete via before() bypass.
     */
    public function delete(User $user, ProviderLink $link): bool
    {
        return $user->hasRole('provider')
            && $link->profile->user_id === $user->id;
    }
}
