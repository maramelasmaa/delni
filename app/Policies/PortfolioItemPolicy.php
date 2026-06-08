<?php

namespace App\Policies;

use App\Models\PortfolioItem;
use App\Models\User;
use App\Services\ProfileVisibilityService;

class PortfolioItemPolicy
{
    public function __construct(
        private ProfileVisibilityService $visibility,
    ) {}

    /**
     * Admin bypasses all portfolio item checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Any user or guest can view portfolio items listing.
     * Actual visibility filtering handled at query level.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Portfolio items inherit profile visibility.
     * Guests can view portfolio items on visible profiles only if item is active.
     * Profile owner always sees their own portfolio items.
     */
    public function view(?User $user, PortfolioItem $item): bool
    {
        $profile = $item->profile;

        if (! $profile) {
            return false;
        }

        if ($user && $profile->user_id === $user->id) {
            return true;
        }

        return $item->is_active && $this->visibility->isDiscoverable($profile);
    }

    /**
     * Only providers can create portfolio items.
     * Provider must own the profile they're creating portfolio for.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('provider') && $user->profile !== null;
    }

    /**
     * Only the profile owner (provider) can update their portfolio items.
     */
    public function update(User $user, PortfolioItem $item): bool
    {
        return $user->hasRole('provider')
            && $item->profile->user_id === $user->id;
    }

    /**
     * Only the profile owner (provider) can delete their portfolio items.
     * Admin can delete via before() bypass.
     */
    public function delete(User $user, PortfolioItem $item): bool
    {
        return $user->hasRole('provider')
            && $item->profile->user_id === $user->id;
    }
}
