<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ReviewCreationTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeVisibleProfile(): Profile
    {
        $provider = $this->createProvider(['is_active' => true, 'is_suspended' => false]);

        $profile = $provider->profile;
        $profile->update([
            'is_complete' => true,
            'provider_access_ends_at' => now()->addMonth(),
        ]);

        // ProfileStats is already initialized by ProviderCreationService inside createProvider()

        return $profile->fresh();
    }

    private function makeReviewer(): User
    {
        return $this->createUser(['is_active' => true, 'is_suspended' => false]);
    }

    private function postReview(User $user, Profile $profile, array $data = []): TestResponse
    {
        return $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('review.store', $profile), array_merge([
                'rating' => 5,
                'comment' => 'Very good service!',
            ], $data));
    }

    private function makeReview(User $user, Profile $profile, array $overrides = []): Review
    {
        return Review::create(array_merge([
            'profile_id' => $profile->id,
            'user_id' => $user->id,
            'rating' => 4,
            'status' => ReviewStatus::APPROVED,
            'comment' => 'Good service.',
        ], $overrides));
    }

    // -----------------------------------------------------------------------
    // 1. Basic creation
    // -----------------------------------------------------------------------

    public function test_user_can_submit_review_for_visible_provider(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeReviewer();

        $this->postReview($user, $profile)
            ->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas(Review::class, [
            'profile_id' => $profile->id,
            'user_id' => $user->id,
            'status' => ReviewStatus::APPROVED->value,
        ]);
    }

    // -----------------------------------------------------------------------
    // 2. Approved review blocks resubmission
    // -----------------------------------------------------------------------

    public function test_user_with_approved_review_cannot_submit_another(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeReviewer();
        $this->makeReview($user, $profile);

        $this->actingAs($user)
            ->post(route('review.store', $profile), [
                'rating' => 5,
                'comment' => 'Trying to review again.',
            ])
            ->assertSessionHasErrors(['profile']);

        $this->assertSame(1, Review::where('profile_id', $profile->id)
            ->where('user_id', $user->id)
            ->count());
    }

    // -----------------------------------------------------------------------
    // 3. Rejected review allows resubmission (Option B)
    // -----------------------------------------------------------------------

    public function test_user_with_rejected_review_can_submit_new_review(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeReviewer();

        $this->makeReview($user, $profile, ['status' => ReviewStatus::REJECTED]);

        $this->postReview($user, $profile, ['rating' => 4, 'comment' => 'Trying again.'])
            ->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertSame(1, Review::where('profile_id', $profile->id)
            ->where('user_id', $user->id)
            ->where('status', ReviewStatus::APPROVED->value)
            ->count());
    }

    // -----------------------------------------------------------------------
    // 4. Soft-deleted review allows resubmission
    // -----------------------------------------------------------------------

    public function test_user_with_soft_deleted_review_can_submit_new_review(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeReviewer();

        $this->makeReview($user, $profile)->delete();

        $this->postReview($user, $profile, ['rating' => 3, 'comment' => 'Second attempt.'])
            ->assertSuccessful();

        $this->assertDatabaseHas(Review::class, [
            'profile_id' => $profile->id,
            'user_id' => $user->id,
            'status' => ReviewStatus::APPROVED->value,
            'deleted_at' => null,
        ]);
    }

    // -----------------------------------------------------------------------
    // 5. Stats: rejected review does not count
    // -----------------------------------------------------------------------

    public function test_rejected_review_does_not_count_in_rating_stats(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeReviewer();

        $review = $this->makeReview($user, $profile, ['rating' => 1]);
        app(ReviewModerationService::class)->reject($review);

        $stats = $profile->fresh()->stats;

        $this->assertSame(0, $stats->reviews_count);
        $this->assertSame(0.0, (float) $stats->rating_avg);
    }

    // -----------------------------------------------------------------------
    // 6. Admin accepts flag: review hidden, stats recalculated
    // -----------------------------------------------------------------------

    public function test_admin_accept_flag_hides_review_and_recalculates_stats(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeReviewer();
        $review = $this->makeReview($user, $profile, ['rating' => 5]);

        $review->update([
            'is_flagged' => true,
            'flagged_by' => $user->id,
            'flagged_at' => now(),
            'flagged_reason' => 'Inappropriate',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        app(ReviewModerationService::class)->acceptFlag($review);

        $review->refresh();
        $this->assertSame(ReviewStatus::REJECTED, $review->status);
        $this->assertNotNull($review->flag_handled_at);

        $stats = $profile->fresh()->stats;
        $this->assertSame(0, $stats->reviews_count);
        $this->assertSame(0.0, (float) $stats->rating_avg);
    }

    // -----------------------------------------------------------------------
    // 7. Admin rejects flag: review stays approved, stats unchanged
    // -----------------------------------------------------------------------

    public function test_admin_reject_flag_keeps_review_approved_and_stats_unchanged(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeReviewer();
        $review = $this->makeReview($user, $profile, ['rating' => 4]);

        $review->update([
            'is_flagged' => true,
            'flagged_by' => $user->id,
            'flagged_at' => now(),
            'flagged_reason' => 'Disagree with review',
        ]);

        app(ReviewModerationService::class)->rejectFlag($review);

        $review->refresh();
        $this->assertSame(ReviewStatus::APPROVED, $review->status);
        $this->assertFalse($review->is_flagged);

        $stats = $profile->fresh()->stats;
        $this->assertSame(1, $stats->reviews_count);
        $this->assertSame(4.0, (float) $stats->rating_avg);
    }

    // -----------------------------------------------------------------------
    // 8. Provider cannot review own profile
    // -----------------------------------------------------------------------

    public function test_provider_cannot_review_own_profile(): void
    {
        $profile = $this->makeVisibleProfile();
        $providerUser = $profile->user;

        $this->actingAs($providerUser)
            ->post(route('review.store', $profile), [
                'rating' => 5,
                'comment' => 'Self-review.',
            ])
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // 9. Provider role cannot create reviews on other profiles
    // -----------------------------------------------------------------------

    public function test_provider_cannot_review_another_provider_profile(): void
    {
        $targetProfile = $this->makeVisibleProfile();
        $otherProvider = $this->createProvider();

        $this->actingAs($otherProvider)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('review.store', $targetProfile), [
                'rating' => 3,
                'comment' => 'Review from provider.',
            ])
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // 10. Guest is redirected to login
    // -----------------------------------------------------------------------

    public function test_guest_cannot_submit_review_and_is_redirected(): void
    {
        $profile = $this->makeVisibleProfile();

        $this->post(route('review.store', $profile), [
            'rating' => 5,
            'comment' => 'Guest review attempt.',
        ])->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    // 11. Daily review limit
    // -----------------------------------------------------------------------

    public function test_daily_review_limit_blocks_eleventh_review(): void
    {
        $user = $this->makeReviewer();

        // Directly create 10 reviews for today (bypass HTTP to avoid rate limiter)
        for ($i = 0; $i < 10; $i++) {
            $profile = $this->makeVisibleProfile();
            Review::create([
                'profile_id' => $profile->id,
                'user_id' => $user->id,
                'rating' => 5,
                'status' => ReviewStatus::APPROVED,
                'created_at' => now(),
            ]);
        }

        // 11th should fail
        $freshProfile = $this->makeVisibleProfile();
        $this->postReview($user, $freshProfile)
            ->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // 12. Flagged but unhandled review still counts in stats
    // -----------------------------------------------------------------------

    public function test_flagged_pending_review_still_counts_in_stats(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeReviewer();

        $review = $this->makeReview($user, $profile, ['rating' => 5]);
        $review->update([
            'is_flagged' => true,
            'flagged_by' => $user->id,
            'flagged_at' => now(),
            'flagged_reason' => 'Spam',
        ]);

        // Status is still APPROVED — flag decision pending
        $this->assertSame(ReviewStatus::APPROVED, $review->fresh()->status);

        $stats = $profile->fresh()->stats;
        $this->assertSame(1, $stats->reviews_count);
        $this->assertSame(5.0, (float) $stats->rating_avg);
    }
}
