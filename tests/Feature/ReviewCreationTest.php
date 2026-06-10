<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewCreationTest extends TestCase
{
    private User $reviewer;

    private User $provider;

    private Profile $providerProfile;

    private SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = SubscriptionPlan::factory()->create();

        // Create provider with active subscription using test helper
        $this->provider = $this->createProvider();
        $this->providerProfile = $this->provider->profile;

        Subscription::factory()
            ->for($this->provider)
            ->for($this->plan)
            ->active()
            ->create([
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
            ]);

        // Create reviewer using test helper
        $this->reviewer = $this->createUser();
    }

    #[Test]
    public function can_create_review_when_eligible(): void
    {
        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile), [
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

    #[Test]
    public function cannot_create_review_when_account_too_new(): void
    {
        // Reviewer account created less than 24 hours ago
        $newReviewer = $this->createUser([
            'created_at' => now()->subHours(12),
        ]);

        $this->actingAs($newReviewer);

        $response = $this->post(route('review.store', $this->providerProfile), [
            'rating' => 5,
            'comment' => 'Test',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing(Review::class, [
            'user_id' => $newReviewer->id,
        ]);
    }

    #[Test]
    public function cannot_create_review_when_exceeded_daily_limit(): void
    {
        // Create 10 reviews for today
        Review::factory(10)
            ->for($this->providerProfile)
            ->for($this->reviewer)
            ->create([
                'created_at' => now(),
            ]);

        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile), [
            'rating' => 5,
            'comment' => 'Should fail',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    #[Test]
    public function cannot_create_duplicate_review(): void
    {
        Review::factory()
            ->for($this->providerProfile)
            ->for($this->reviewer)
            ->create();

        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile), [
            'rating' => 4,
            'comment' => 'Already reviewed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertCount(1, Review::where('user_id', $this->reviewer->id)->get());
    }

    #[Test]
    public function cannot_review_own_profile(): void
    {
        $provider = $this->createProvider();
        $profile = $provider->profile;

        Subscription::factory()
            ->for($provider)
            ->for($this->plan)
            ->active()
            ->create();

        $this->actingAs($provider);

        $response = $this->post(route('review.store', $profile), [
            'rating' => 5,
            'comment' => 'Cannot review self',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function cannot_review_incomplete_profile(): void
    {
        $incompleteProfile = Profile::factory()
            ->for($this->provider)
            ->incomplete()
            ->create();

        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $incompleteProfile), [
            'rating' => 5,
            'comment' => 'Should fail',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    #[Test]
    public function cannot_review_when_subscription_expired(): void
    {
        // Expire the subscription
        Subscription::where('user_id', $this->provider->id)
            ->update([
                'is_active' => false,
                'ends_at' => now()->subDay(),
            ]);

        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile), [
            'rating' => 5,
            'comment' => 'Should fail',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    #[Test]
    public function cannot_review_when_suspended(): void
    {
        $this->reviewer->update(['is_suspended' => true]);

        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile), [
            'rating' => 5,
            'comment' => 'Should fail',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    #[Test]
    public function cannot_review_when_inactive(): void
    {
        $this->reviewer->update(['is_active' => false]);

        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile), [
            'rating' => 5,
            'comment' => 'Should fail',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    #[Test]
    public function cannot_review_when_account_locked(): void
    {
        $this->reviewer->update([
            'locked_until' => now()->addHour(),
        ]);

        $this->actingAs($this->reviewer);

        $response = $this->post(route('review.store', $this->providerProfile), [
            'rating' => 5,
            'comment' => 'Should fail',
        ]);

        $response->assertRedirect();
    }

    #[Test]
    public function provider_cannot_create_reviews(): void
    {
        $this->actingAs($this->provider);

        $otherProvider = User::factory()->create();
        $otherProvider->assignRole('provider');
        $otherProfile = Profile::factory()->for($otherProvider)->complete()->create();

        Subscription::factory()
            ->for($otherProvider)
            ->for($this->plan)
            ->active()
            ->create();

        $response = $this->post(route('review.store', $otherProfile), [
            'rating' => 5,
            'comment' => 'Should fail',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function guest_cannot_create_review(): void
    {
        $response = $this->post(route('review.store', $this->providerProfile), [
            'rating' => 5,
            'comment' => 'Should fail',
        ]);

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function review_observer_triggers_stat_recalculation(): void
    {
        $this->actingAs($this->reviewer);

        $initialCount = $this->providerProfile->stats->review_count ?? 0;

        $this->post(route('review.store', $this->providerProfile), [
            'rating' => 5,
            'comment' => 'Great service',
        ]);

        $this->providerProfile->stats->refresh();
        $newCount = $this->providerProfile->stats->review_count ?? 0;

        $this->assertGreaterThan($initialCount, $newCount);
    }
}
