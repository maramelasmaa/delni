<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Enums\ReviewStatus;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FlagReviewTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function makeVisibleProfile(User $provider): Profile
    {
        return Profile::factory([
            'user_id' => $provider->id,
            'provider_access_ends_at' => now()->addYear(),
        ])->complete()->create();
    }

    public function test_provider_can_flag_review_on_own_profile(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $reviewer->id])->create();

        $response = $this->actingAs($provider)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => 'This review violates our community guidelines and is inappropriate.',
            ]);

        $response->assertOk();
        $this->assertTrue($review->refresh()->is_flagged);
        $this->assertEquals($provider->id, $review->flagged_by);
        $this->assertNotNull($review->flagged_at);
        $this->assertEquals('This review violates our community guidelines and is inappropriate.', $review->flagged_reason);
    }

    public function test_provider_cannot_flag_review_on_another_provider_profile(): void
    {
        $provider1 = User::factory()->create();
        $provider1->assignRole('provider');
        $provider2 = User::factory()->create();
        $provider2->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider2);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $reviewer->id])->create();

        $response = $this->actingAs($provider1)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => 'This review violates our community guidelines.',
            ]);

        $response->assertForbidden();
        $this->assertFalse($review->refresh()->is_flagged);
    }

    public function test_provider_cannot_flag_own_review(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $provider->id])->create();

        $response = $this->actingAs($provider)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => 'This review violates our community guidelines.',
            ]);

        $response->assertForbidden();
        $this->assertFalse($review->refresh()->is_flagged);
    }

    public function test_user_can_flag_any_visible_review(): void
    {
        $user1 = User::factory()->create();
        $user1->assignRole('user');
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $reviewer->id])->create();

        $response = $this->actingAs($user1)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => 'This review contains inappropriate language and offensive content.',
            ]);

        $response->assertOk();
        $this->assertTrue($review->refresh()->is_flagged);
        $this->assertEquals($user1->id, $review->flagged_by);
    }

    public function test_flag_requires_minimum_reason_length(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $reviewer->id])->create();

        $response = $this->actingAs($provider)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => 'Too short',
            ]);

        $response->assertJsonValidationErrors('reason');
        $this->assertFalse($review->refresh()->is_flagged);
    }

    public function test_flag_requires_reason(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $reviewer->id])->create();

        $response = $this->actingAs($provider)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => '',
            ]);

        $response->assertJsonValidationErrors('reason');
        $this->assertFalse($review->refresh()->is_flagged);
    }

    public function test_suspended_user_cannot_flag_review(): void
    {
        $provider = User::factory()->create(['is_suspended' => true]);
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $reviewer->id])->create();

        $response = $this->actingAs($provider)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => 'This review violates our community guidelines and is inappropriate.',
            ]);

        // Suspended users are blocked by EnsureUserNotSuspended middleware with 403
        $response->assertForbidden();
        $this->assertFalse($review->refresh()->is_flagged);
    }

    public function test_flagged_review_remains_public_before_moderation(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $reviewer->id])->create();

        $this->actingAs($provider)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => 'This review violates our community guidelines.',
            ]);

        $review->refresh();
        $this->assertTrue($review->is_flagged);
        $this->assertNull($review->flag_handled_at);
        $this->assertTrue($review->isApproved());
    }

    public function test_cannot_flag_already_flagged_review(): void
    {
        $provider1 = User::factory()->create();
        $provider1->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider1);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $reviewer->id, 'is_flagged' => true])->create();

        $response = $this->actingAs($provider1)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => 'Another reason for flagging.',
            ]);

        $response->assertOk();
        $this->assertTrue($review->refresh()->is_flagged);
    }

    public function test_reflagging_clears_previous_admin_decision_fields(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);

        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');

        $firstFlagger = User::factory()->create();
        $firstFlagger->assignRole('user');

        $review = Review::factory([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'status' => ReviewStatus::REJECTED,
            'is_flagged' => true,
            'flagged_by' => $firstFlagger->id,
            'flagged_at' => now()->subDay(),
            'flagged_reason' => 'Old flag reason.',
            'flag_handled_at' => now()->subHours(2),
            'flag_handled_by' => $provider->id,
            'moderated_at' => now()->subHours(2),
            'moderated_by' => $provider->id,
            'moderation_note' => 'Old admin decision.',
        ])->create();

        $response = $this->actingAs($provider)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => 'A fresh reason that should reopen review moderation.',
            ]);

        $response->assertOk();

        $review->refresh();

        $this->assertTrue($review->is_flagged);
        $this->assertSame($provider->id, $review->flagged_by);
        $this->assertSame('A fresh reason that should reopen review moderation.', $review->flagged_reason);
        $this->assertNull($review->flag_handled_at);
        $this->assertNull($review->flag_handled_by);
        $this->assertNull($review->moderated_at);
        $this->assertNull($review->moderated_by);
        $this->assertNull($review->moderation_note);
    }

    public function test_flagger_can_see_admin_response_in_reviews_api(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);

        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');

        $flagger = User::factory()->create();
        $flagger->assignRole('user');

        $review = Review::factory([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'status' => ReviewStatus::APPROVED,
            'is_flagged' => false,
            'flagged_by' => $flagger->id,
            'flagged_at' => now()->subHours(3),
            'flagged_reason' => 'This sounds misleading to customers.',
            'flag_handled_at' => now()->subHour(),
            'moderation_note' => 'We reviewed it and decided the review can remain visible.',
        ])->create();

        $this->actingAs($flagger, 'sanctum')
            ->getJson("/api/v1/providers/{$profile->slug}/reviews")
            ->assertOk()
            ->assertJsonPath('data.0.id', $review->id)
            ->assertJsonPath('data.0.flagged_reason', 'This sounds misleading to customers.')
            ->assertJsonPath('data.0.flag_response', 'rejected')
            ->assertJsonPath('data.0.moderation_note', 'We reviewed it and decided the review can remain visible.');
    }

    public function test_unauthenticated_user_cannot_flag(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $reviewer->id])->create();

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('api.reviews.flag', $review), [
                'reason' => 'This review violates our community guidelines.',
            ]);

        $response->assertUnauthorized();
        $this->assertFalse($review->refresh()->is_flagged);
    }
}
