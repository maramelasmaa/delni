<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use App\Services\ProfileVisibilityService;

class ReviewPolicy
{
    public function __construct(
        private ProfileVisibilityService $visibility,
    ) {}

    /**
     * Admin bypasses all review checks.
     * No excluded abilities here — admin can soft-delete and edit reviews
     * through Filament moderation panel per SRS.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (in_array($ability, ['create', 'flag'], true)) {
            return null;
        }

        if ($user->hasAnyRole(['super_admin', 'app_review_moderator'])) {
            return true;
        }

        return null;
    }

    /**
     * Any user or guest can view the reviews listing.
     * Actual visibility filtering handled at query level.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Reviews inherit profile visibility.
     * Guests can view reviews on visible profiles.
     * Profile owner always sees their own profile reviews.
     *
     * Invariant: reviews not accessible when profile is hidden.
     */
    public function view(?User $user, Review $review): bool
    {
        $profile = $review->profile;

        if (! $profile) {
            return false;
        }

        if ($user && $profile->user_id === $user->id) {
            return true;
        }

        return $this->visibility->isDiscoverable($profile);
    }

    /**
     * Only 'user' role can create reviews IF:
     * - Not reviewing their own profile — R2
     * - Target profile is publicly visible — R1
     *
     * Providers cannot review, admins cannot create public reviews.
     * One review per user per profile enforced at DB + Form Request.
     *
     * Eligibility checks (is_active, is_suspended, account age) are validated
     * in CreateReviewRequest::withValidator() to provide a 422 validation error
     * instead of a 403 authorization error — better UX per SRS.
     *
     * Controller: $this->authorize('create', [Review::class, $profile])
     *
     * Invariants: R1, R2, R5.
     */
    public function create(User $user, Profile $profile): bool
    {
        if (! $user->hasRole('user')) {
            return false;
        }

        if ($profile->user_id === $user->id) {
            return false;
        }

        return $this->visibility->isDiscoverable($profile);
    }

    /**
     * Nobody can update their own review after submission — ever.
     * Admin can edit via before() bypass.
     * Invariant: reviewer cannot edit after submission.
     */
    public function update(User $user, Review $review): bool
    {
        return false;
    }

    /**
     * Nobody can delete reviews directly through a route.
     * Admin soft-delete handled by before() bypass.
     * Invariant: soft-delete only, admin only.
     */
    public function delete(User $user, Review $review): bool
    {
        return false;
    }

    /**
     * Flag rules — business rules document section 8:
     *
     * - Cannot flag own review — any role
     * - Review must belong to a visible profile
     * - Provider: can only flag reviews on their OWN profile
     * - Public user (role 'user'): can flag any review on any visible profile
     *   except their own
     * - Explicit hasRole('user') check — more precise than return true,
     *   prevents accidental access if new roles are added later
     *
     * Eligibility checks (is_suspended, is_active) validated in FlagReviewRequest::withValidator()
     * to provide 422 validation error instead of 403 authorization error — better UX per SRS.
     *
     * Admin handled by before() bypass.
     */
    public function flag(User $user, Review $review): bool
    {
        // Cannot flag own review
        if ($review->user_id === $user->id) {
            return false;
        }

        // Review must belong to a visible, discoverable profile
        $profile = $review->profile;

        if (! $profile || ! $this->visibility->isDiscoverable($profile)) {
            return false;
        }

        if ($user->hasRole('provider')) {
            $ownProfile = $user->profile;

            return $ownProfile !== null
                && $review->profile_id === $ownProfile->id;
        }

        // Explicitly check 'user' role — not just return true
        // Prevents unintended access if new roles are introduced
        return $user->hasRole('user');
    }

    /**
     * Moderation is admin-only.
     * Admin handled by before() bypass.
     */
    public function moderate(User $user, Review $review): bool
    {
        return false;
    }
}
