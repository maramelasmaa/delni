<?php

namespace App\Policies;

use App\Models\PortfolioImage;
use App\Models\User;
use App\Services\ProfileVisibilityService;

class PortfolioImagePolicy
{
    public function __construct(
        private ProfileVisibilityService $visibility,
    ) {}

    /**
     * Admin bypasses all portfolio image checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    /**
     * Any user or guest can view portfolio images listing.
     * Actual visibility filtering handled at query level.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Portfolio images inherit portfolio item visibility.
     * Guests can view images only if portfolio item is active and profile is discoverable.
     * Profile owner always sees their own portfolio images.
     */
    public function view(?User $user, PortfolioImage $image): bool
    {
        $portfolioItem = $image->portfolioItem;

        if (! $portfolioItem) {
            return false;
        }

        $profile = $portfolioItem->profile;

        if (! $profile) {
            return false;
        }

        if ($user && $profile->user_id === $user->id) {
            return true;
        }

        return $portfolioItem->is_active && $this->visibility->isDiscoverable($profile);
    }

    /**
     * Only providers can create portfolio images.
     * Provider must own the portfolio item they're uploading images for.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('provider') && $user->profile !== null;
    }

    /**
     * Only the portfolio item owner (provider) can update/view images in the repeater.
     */
    public function update(User $user, PortfolioImage $image): bool
    {
        return $user->hasRole('provider')
            && $image->portfolioItem->profile->user_id === $user->id;
    }

    /**
     * Only the portfolio item owner (provider) can delete their portfolio images.
     * Admin can delete via before() bypass.
     */
    public function delete(User $user, PortfolioImage $image): bool
    {
        return $user->hasRole('provider')
            && $image->portfolioItem->profile->user_id === $user->id;
    }
}
