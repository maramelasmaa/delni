<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected User $reviewer;

    protected User $provider;

    protected Profile $providerProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reviewer = User::factory()
            ->has(Profile::factory())
            ->create();
        $this->reviewer->assignRole('user');

        $this->provider = User::factory()
            ->has(Profile::factory())
            ->create();
        $this->provider->assignRole('provider');

        $this->providerProfile = $this->provider->profile;
    }

    // ════════════════════════════════════════════════════════════
    // REVIEW CREATION TESTS
    // ════════════════════════════════════════════════════════════

    public function test_user_can_create_immediate_approved_review(): void
    {
        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile->slug), [
            'rating' => 5,
            'comment' => 'Excellent service!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas(Review::class, [
            'profile_id' => $this->providerProfile->id,
            'user_id' => $this->reviewer->id,
            'rating' => 5,
            'status' => ReviewStatus::APPROVED->value,
        ]);
    }

    public function test_review_appears_publicly_immediately(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->for($this->reviewer)
            ->create(['status' => ReviewStatus::APPROVED]);

        $response = $this->get(route('public.provider', $this->providerProfile->slug));

        $this->assertStringContainsString($review->comment, $response->getContent());
    }

    public function test_duplicate_review_blocked(): void
    {
        Review::factory()
            ->for($this->providerProfile)
            ->for($this->reviewer)
            ->create(['status' => ReviewStatus::APPROVED]);

        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile->slug), [
            'rating' => 4,
            'comment' => 'Second review attempt',
        ]);

        $response->assertSessionHasErrors('profile');
        $this->assertEquals(1, $this->providerProfile->reviews()->count());
    }

    public function test_duplicate_check_includes_soft_deleted_reviews(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->for($this->reviewer)
            ->create(['status' => ReviewStatus::APPROVED]);

        $review->delete();

        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile->slug), [
            'rating' => 4,
            'comment' => 'Attempting duplicate after deletion',
        ]);

        $response->assertSessionHasErrors('profile');
    }

    public function test_provider_cannot_review_own_profile(): void
    {
        $this->actingAs($this->provider);

        $response = $this->post(route('review.store', $this->providerProfile->slug), [
            'rating' => 5,
            'comment' => 'Self review',
        ]);

        $response->assertForbidden();
    }

    public function test_guest_cannot_create_review(): void
    {
        $response = $this->post(route('review.store', $this->providerProfile->slug), [
            'rating' => 5,
            'comment' => 'Guest review',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_suspended_user_blocked(): void
    {
        $this->reviewer->update(['is_suspended' => true]);
        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile->slug), [
            'rating' => 5,
            'comment' => 'Suspended user review',
        ]);

        $response->assertSessionHasErrors('profile');
    }

    public function test_inactive_user_blocked(): void
    {
        $this->reviewer->update(['is_active' => false]);
        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile->slug), [
            'rating' => 5,
            'comment' => 'Inactive user review',
        ]);

        $response->assertSessionHasErrors('profile');
    }

    public function test_locked_user_blocked(): void
    {
        $this->reviewer->update(['locked_until' => now()->addHours(1)]);
        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile->slug), [
            'rating' => 5,
            'comment' => 'Locked user review',
        ]);

        $response->assertStatus(403);
    }

    public function test_review_count_includes_only_approved_and_active(): void
    {
        Review::factory(3)
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::APPROVED]);

        Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::REJECTED]);

        Review::factory()
            ->for($this->providerProfile)
            ->trashed()
            ->create(['status' => ReviewStatus::APPROVED]);

        $this->assertEquals(3, $this->providerProfile->stats->reviews_count);
    }

    public function test_rating_average_excludes_rejected_and_deleted(): void
    {
        Review::factory()->for($this->providerProfile)->create(['rating' => 5, 'status' => ReviewStatus::APPROVED]);
        Review::factory()->for($this->providerProfile)->create(['rating' => 4, 'status' => ReviewStatus::APPROVED]);
        Review::factory()->for($this->providerProfile)->create(['rating' => 1, 'status' => ReviewStatus::REJECTED]);
        Review::factory()->for($this->providerProfile)->trashed()->create(['rating' => 5, 'status' => ReviewStatus::APPROVED]);

        $expectedAvg = (5 + 4) / 2;
        $this->assertEquals($expectedAvg, $this->providerProfile->stats->rating_avg);
    }

    // ════════════════════════════════════════════════════════════
    // PROVIDER FLAGGING TESTS
    // ════════════════════════════════════════════════════════════

    public function test_provider_can_flag_review_on_own_profile(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::APPROVED]);

        $this->actingAs($this->provider);

        $response = $this->post(route('reviews.flag', $review), [
            'reason' => 'This review is offensive and inappropriate',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas(Review::class, [
            'id' => $review->id,
            'is_flagged' => true,
            'flagged_by' => $this->provider->id,
        ]);
    }

    public function test_provider_cannot_flag_review_on_other_provider_profile(): void
    {
        $otherProvider = User::factory()
            ->has(Profile::factory())
            ->create();
        $otherProvider->assignRole('provider');

        $review = Review::factory()
            ->for($otherProvider->profile)
            ->create(['status' => ReviewStatus::APPROVED]);

        $this->actingAs($this->provider);

        $response = $this->post(route('reviews.flag', $review), [
            'reason' => 'Attempting to flag review on other provider profile',
        ]);

        $response->assertForbidden();
        $this->assertFalse($review->fresh()->is_flagged);
    }

    public function test_provider_must_provide_reason_when_flagging(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::APPROVED]);

        $this->actingAs($this->provider);

        $response = $this->post(route('reviews.flag', $review), [
            'reason' => '',
        ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_flagging_reason_must_be_at_least_10_characters(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::APPROVED]);

        $this->actingAs($this->provider);

        $response = $this->post(route('reviews.flag', $review), [
            'reason' => 'Short',
        ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_flagging_reason_cannot_exceed_1000_characters(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::APPROVED]);

        $this->actingAs($this->provider);

        $response = $this->post(route('reviews.flag', $review), [
            'reason' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_flagged_review_remains_public_before_admin_decision(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::APPROVED]);

        $this->actingAs($this->provider);
        $this->post(route('reviews.flag', $review), [
            'reason' => 'This review is inappropriate',
        ]);

        $response = $this->get(route('public.provider', $this->providerProfile->slug));
        $this->assertStringContainsString($review->comment, $response->getContent());
    }

    public function test_flagged_review_still_counts_in_ratings(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->create(['rating' => 5, 'status' => ReviewStatus::APPROVED]);

        $this->actingAs($this->provider);
        $this->post(route('reviews.flag', $review), [
            'reason' => 'This review is inappropriate',
        ]);

        $this->assertEquals(1, $this->providerProfile->stats->reviews_count);
        $this->assertEquals(5, $this->providerProfile->stats->rating_avg);
    }

    // ════════════════════════════════════════════════════════════
    // ADMIN FLAG MODERATION TESTS
    // ════════════════════════════════════════════════════════════

    public function test_admin_accepting_flag_hides_review(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $review = Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::APPROVED, 'is_flagged' => true, 'flagged_at' => now()]);

        $this->actingAs($admin);

        // Assuming admin uses service via Filament action
        $service = app(ReviewModerationService::class);
        $service->acceptFlag($review);

        $review->refresh();
        $this->assertEquals(ReviewStatus::REJECTED->value, $review->status);
        $this->assertTrue($review->is_flagged);
        $this->assertNotNull($review->flag_handled_at);
    }

    public function test_admin_accepting_flag_removes_from_ratings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $review = Review::factory()
            ->for($this->providerProfile)
            ->create([
                'rating' => 5,
                'status' => ReviewStatus::APPROVED,
                'is_flagged' => true,
            ]);

        $this->actingAs($admin);
        $service = app(ReviewModerationService::class);
        $service->acceptFlag($review);

        // Refresh stats (normally done by observer or job)
        $this->providerProfile->stats->recalculate();

        $this->assertEquals(0, $this->providerProfile->stats->reviews_count);
        $this->assertNull($this->providerProfile->stats->rating_avg);
    }

    public function test_admin_rejecting_flag_keeps_review_public(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $review = Review::factory()
            ->for($this->providerProfile)
            ->create([
                'status' => ReviewStatus::APPROVED,
                'is_flagged' => true,
                'flagged_at' => now(),
            ]);

        $this->actingAs($admin);
        $service = app(ReviewModerationService::class);
        $service->rejectFlag($review);

        $review->refresh();
        $this->assertEquals(ReviewStatus::APPROVED->value, $review->status);
        $this->assertFalse($review->is_flagged);
        $this->assertNotNull($review->flag_handled_at);
    }

    public function test_admin_rejecting_flag_keeps_review_in_ratings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $review = Review::factory()
            ->for($this->providerProfile)
            ->create([
                'rating' => 4,
                'status' => ReviewStatus::APPROVED,
                'is_flagged' => true,
            ]);

        $this->actingAs($admin);
        $service = app(ReviewModerationService::class);
        $service->rejectFlag($review);

        $this->providerProfile->stats->recalculate();

        $this->assertEquals(1, $this->providerProfile->stats->reviews_count);
        $this->assertEquals(4, $this->providerProfile->stats->rating_avg);
    }

    public function test_accepted_flag_removed_from_active_queue(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $review = Review::factory()
            ->for($this->providerProfile)
            ->create([
                'status' => ReviewStatus::APPROVED,
                'is_flagged' => true,
                'flagged_at' => now(),
                'flag_handled_at' => null,
            ]);

        $this->actingAs($admin);
        $service = app(ReviewModerationService::class);
        $service->acceptFlag($review);

        $unhandledFlags = Review::where('is_flagged', true)
            ->whereNull('flag_handled_at')
            ->count();

        $this->assertEquals(0, $unhandledFlags);
    }

    public function test_rejected_flag_removed_from_active_queue(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $review = Review::factory()
            ->for($this->providerProfile)
            ->create([
                'status' => ReviewStatus::APPROVED,
                'is_flagged' => true,
                'flagged_at' => now(),
                'flag_handled_at' => null,
            ]);

        $this->actingAs($admin);
        $service = app(ReviewModerationService::class);
        $service->rejectFlag($review);

        $unhandledFlags = Review::where('is_flagged', true)
            ->whereNull('flag_handled_at')
            ->count();

        $this->assertEquals(0, $unhandledFlags);
    }

    // ════════════════════════════════════════════════════════════
    // SECURITY & AUTHORIZATION TESTS
    // ════════════════════════════════════════════════════════════

    public function test_provider_cannot_delete_review(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::APPROVED]);

        $this->actingAs($this->provider);

        $response = $this->delete(route('reviews.destroy', $review) ?? '/reviews/'.$review->id);

        // Should either 404 or 403 depending on route existence
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(404),
                $this->equalTo(403),
            ),
        );
    }

    public function test_provider_cannot_edit_review(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::APPROVED]);

        $this->actingAs($this->provider);

        $response = $this->patch(route('reviews.update', $review) ?? '/reviews/'.$review->id, [
            'comment' => 'Updated comment',
        ]);

        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(404),
                $this->equalTo(403),
            ),
        );
    }

    public function test_public_page_never_shows_hidden_reviews(): void
    {
        $approvedReview = Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::APPROVED, 'comment' => 'Visible review']);

        $rejectedReview = Review::factory()
            ->for($this->providerProfile)
            ->create(['status' => ReviewStatus::REJECTED, 'comment' => 'Hidden review']);

        $response = $this->get(route('public.provider', $this->providerProfile->slug));

        $this->assertStringContainsString($approvedReview->comment, $response->getContent());
        $this->assertStringNotContainsString($rejectedReview->comment, $response->getContent());
    }

    public function test_deleted_reviews_not_visible_publicly(): void
    {
        $review = Review::factory()
            ->for($this->providerProfile)
            ->trashed()
            ->create(['status' => ReviewStatus::APPROVED]);

        $response = $this->get(route('public.provider', $this->providerProfile->slug));

        $this->assertStringNotContainsString($review->comment, $response->getContent());
    }
}
