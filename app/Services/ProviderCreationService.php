<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

/**
 * Handles synchronous, transactional provider profile creation.
 *
 * This service MUST ensure that provider creation is atomic:
 * Either ALL of the following exist or NONE exist:
 * - User record
 * - Profile record
 * - ProfileStats record
 *
 * This protects against queue worker failures — provider creation
 * must never depend on async queue workers for critical business state.
 *
 * The queue is used ONLY for non-critical secondary work:
 * notifications, analytics, cache warmup, etc.
 */
class ProviderCreationService
{
    public function __construct(
        private readonly ProfileStatsService $statsService,
    ) {}

    /**
     * Create a complete provider profile for an authenticated user.
     *
     * MUST be called within a transaction for safety.
     * This method does NOT start its own transaction — the caller owns the transaction.
     *
     * @throws \Exception if user is not a provider or profile creation fails
     */
    public function createProfileForUser(User $user): Profile
    {
        // Safety checks: Only create profile for providers, not admins
        if ($user->isAdmin()) {
            throw new \Exception('Cannot create profile for admin users');
        }

        if (! $user->hasRole('provider')) {
            throw new \Exception('User must have provider role to create profile');
        }

        // Check if profile already exists (idempotency guard)
        $existing = Profile::where('user_id', $user->id)->first();
        if ($existing) {
            // If profile exists but stats missing, initialize stats
            if (! $existing->stats()->exists()) {
                $this->statsService->initializeForProfile($existing);
            }

            return $existing;
        }

        // Create profile within transaction context (caller manages transaction)
        // Use createOrFirst to handle rare race condition where concurrent request
        // creates same profile between our check and creation
        try {
            $profile = Profile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'slug' => $this->generateUniqueSlug($user),
                    'is_complete' => false,
                    'phone' => '',
                    'whatsapp' => '',
                ]
            );

            // If we got an existing profile (firstOrCreate found it), check stats
            if (! $profile->wasRecentlyCreated && ! $profile->stats()->exists()) {
                $this->statsService->initializeForProfile($profile);
            }

            // If this is a newly created profile, initialize stats
            if ($profile->wasRecentlyCreated) {
                $this->statsService->initializeForProfile($profile);
            }

            return $profile;
        } catch (QueryException $e) {
            // If slug collision (rare), regenerate and retry once
            if ($e->errorInfo[1] === 1062 && str_contains($e->getMessage(), 'slug')) {
                return $this->createProfileForUser($user);
            }

            // For any other database error, propagate
            throw $e;
        }
    }

    /**
     * Generate a unique slug for a profile.
     *
     * Strategy: Use a combination of timestamp and random string to minimize collisions.
     * This is NOT a friendly slug — it's an opaque identifier that guarantees uniqueness.
     *
     * Format: profile-{timestamp}-{random}
     * Example: profile-1717662234-aBcD1234
     */
    private function generateUniqueSlug(User $user): string
    {
        // Ensure uniqueness with high probability by incorporating timestamp
        $timestamp = time();
        $random = Str::lower(Str::random(8));

        $slug = "profile-{$timestamp}-{$random}";

        // Verify this slug doesn't already exist
        $attempts = 0;
        while (Profile::where('slug', $slug)->exists() && $attempts < 5) {
            $random = Str::lower(Str::random(8));
            $slug = "profile-{$timestamp}-{$random}";
            $attempts++;
        }

        if ($attempts >= 5) {
            throw new \Exception('Unable to generate unique profile slug after 5 attempts');
        }

        return $slug;
    }
}
