<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

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
            ->post(route('reviews.flag', $review), [
                'reason' => 'This review violates our community guidelines and is inappropriate.',
            ]);

        $response->assertRedirect();
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
            ->post(route('reviews.flag', $review), [
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
            ->post(route('reviews.flag', $review), [
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
            ->post(route('reviews.flag', $review), [
                'reason' => 'This review contains inappropriate language and offensive content.',
            ]);

        $response->assertRedirect();
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
            ->post(route('reviews.flag', $review), [
                'reason' => 'Too short',
            ]);

        $response->assertSessionHasErrors('reason');
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
            ->post(route('reviews.flag', $review), [
                'reason' => '',
            ]);

        $response->assertSessionHasErrors('reason');
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
            ->post(route('reviews.flag', $review), [
                'reason' => 'This review violates our community guidelines and is inappropriate.',
            ]);

        // Suspended users' profiles are not discoverable, so policy denies with 403
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
            ->post(route('reviews.flag', $review), [
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
            ->post(route('reviews.flag', $review), [
                'reason' => 'Another reason for flagging.',
            ]);

        $response->assertRedirect();
        $this->assertTrue($review->refresh()->is_flagged);
    }

    public function test_unauthenticated_user_cannot_flag(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $profile = $this->makeVisibleProfile($provider);
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');
        $review = Review::factory(['profile_id' => $profile->id, 'user_id' => $reviewer->id])->create();

        $response = $this->post(route('reviews.flag', $review), [
            'reason' => 'This review violates our community guidelines.',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertFalse($review->refresh()->is_flagged);
    }
}
