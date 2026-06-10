<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewFlaggingTest extends TestCase
{
    private User $reviewer;

    private User $provider;

    private User $flagger;

    private Profile $providerProfile;

    private Review $review;

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
            ->create();

        // Create reviewer using test helper
        $this->reviewer = $this->createUser();

        // Create review to flag
        $this->review = Review::factory()
            ->for($this->providerProfile)
            ->for($this->reviewer)
            ->create();

        // Create flagger using test helper
        $this->flagger = $this->createUser();
    }

    #[Test]
    public function user_can_flag_review_on_other_profile(): void
    {
        $this->actingAs($this->flagger);

        $response = $this->post(route('reviews.flag', $this->review), [
            'reason' => 'This review violates community guidelines and contains inappropriate language',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas(Review::class, [
            'id' => $this->review->id,
            'is_flagged' => true,
            'flagged_by' => $this->flagger->id,
        ]);
    }

    #[Test]
    public function provider_can_flag_review_on_own_profile(): void
    {
        $this->actingAs($this->provider);

        $response = $this->post(route('reviews.flag', $this->review), [
            'reason' => 'This review is false and damages my reputation as a professional',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas(Review::class, [
            'id' => $this->review->id,
            'is_flagged' => true,
        ]);
    }

    #[Test]
    public function provider_cannot_flag_review_on_other_profile(): void
    {
        $otherProvider = $this->createProvider();
        $otherProfile = $otherProvider->profile;

        Subscription::factory()
            ->for($otherProvider)
            ->for($this->plan)
            ->active()
            ->create();

        $otherReview = Review::factory()
            ->for($otherProfile)
            ->for($this->reviewer)
            ->create();

        $this->actingAs($this->provider);

        $response = $this->post(route('reviews.flag', $otherReview), [
            'reason' => 'Should not be allowed',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function cannot_flag_own_review(): void
    {
        $ownReview = Review::factory()
            ->for($this->providerProfile)
            ->for($this->flagger)
            ->create();

        $this->actingAs($this->flagger);

        $response = $this->post(route('reviews.flag', $ownReview), [
            'reason' => 'Cannot flag own review',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function cannot_flag_review_when_suspended(): void
    {
        $this->flagger->update(['is_suspended' => true]);

        $this->actingAs($this->flagger);

        $response = $this->post(route('reviews.flag', $this->review), [
            'reason' => 'Should not be allowed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    #[Test]
    public function cannot_flag_review_on_expired_subscription_profile(): void
    {
        // Expire the provider's subscription
        Subscription::where('user_id', $this->provider->id)
            ->update([
                'is_active' => false,
                'ends_at' => now()->subDay(),
            ]);

        $this->actingAs($this->flagger);

        $response = $this->post(route('reviews.flag', $this->review), [
            'reason' => 'Should not be allowed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    #[Test]
    public function cannot_flag_review_on_incomplete_profile(): void
    {
        $incompleteProfile = Profile::factory()
            ->for($this->provider)
            ->incomplete()
            ->create();

        $incompleteReview = Review::factory()
            ->for($incompleteProfile)
            ->for($this->reviewer)
            ->create();

        $this->actingAs($this->flagger);

        $response = $this->post(route('reviews.flag', $incompleteReview), [
            'reason' => 'Should not be allowed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    #[Test]
    public function guest_cannot_flag_review(): void
    {
        $response = $this->post(route('reviews.flag', $this->review), [
            'reason' => 'Should not be allowed',
        ]);

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function reason_must_be_at_least10_characters(): void
    {
        $this->actingAs($this->flagger);

        $response = $this->post(route('reviews.flag', $this->review), [
            'reason' => 'Short',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('reason');
    }
}
