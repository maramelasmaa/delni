<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Enums\ReviewStatus;
use App\Jobs\RecalculateProfileStatsJob;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewCreationService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReviewCreationTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function makeVisibleProfile(?User $provider = null): Profile
    {
        $provider ??= tap(User::factory()->create(), fn ($u) => $u->assignRole('provider'));

        $profile = Profile::factory([
            'user_id' => $provider->id,
            'provider_access_ends_at' => now()->addYear(),
            'business_name' => 'Test Provider',
            'city_id' => City::factory()->create()->id,
            'category_id' => Category::factory()->create()->id,
            'whatsapp' => '+218912345678',
            'phone' => '+218912345678',
            'experience_years' => 3,
            'is_complete' => true,
        ])->create();

        ProfileStats::firstOrCreate(['profile_id' => $profile->id]);

        return $profile->fresh();
    }

    private function makeUser(array $attributes = []): User
    {
        return tap(User::factory()->create($attributes), fn ($u) => $u->assignRole('user'));
    }

    public function test_newly_created_user_can_review_immediately(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeUser(['created_at' => now()]);

        $response = $this->actingAs($user)->post(route('review.store', $profile), [
            'rating' => 5,
            'comment' => 'Excellent service, highly recommend!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'profile_id' => $profile->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_user_cannot_review_same_profile_twice(): void
    {
        Queue::fake();
        $profile = $this->makeVisibleProfile();
        $user = $this->makeUser();
        $service = app(ReviewCreationService::class);

        $service->create($user, $profile, 5, 'First review');

        $response = $this->actingAs($user)->post(route('review.store', $profile), [
            'rating' => 4,
            'comment' => 'Trying to review again',
        ]);

        $response->assertSessionHasErrors('profile');
        $this->assertSame(1, Review::where('profile_id', $profile->id)->where('user_id', $user->id)->count());
    }

    public function test_user_cannot_review_own_provider_profile(): void
    {
        $provider = tap(User::factory()->create(), fn ($u) => $u->assignRole('provider'));
        $profile = $this->makeVisibleProfile($provider);

        // Provider also has 'user' role to test the own-profile check specifically
        $provider->assignRole('user');

        $response = $this->actingAs($provider)->post(route('review.store', $profile), [
            'rating' => 5,
            'comment' => 'Reviewing my own profile',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('reviews', ['profile_id' => $profile->id, 'user_id' => $provider->id]);
    }

    public function test_provider_cannot_review_publicly(): void
    {
        $provider = tap(User::factory()->create(), fn ($u) => $u->assignRole('provider'));
        $profile = $this->makeVisibleProfile();

        $response = $this->actingAs($provider)->post(route('review.store', $profile), [
            'rating' => 4,
            'comment' => 'A provider trying to leave a review',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('reviews', ['user_id' => $provider->id]);
    }

    public function test_guest_cannot_review(): void
    {
        $profile = $this->makeVisibleProfile();

        $response = $this->post(route('review.store', $profile), [
            'rating' => 5,
            'comment' => 'Guest review attempt',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('reviews', ['profile_id' => $profile->id]);
    }

    public function test_invisible_provider_cannot_be_reviewed(): void
    {
        $provider = tap(User::factory()->create(), fn ($u) => $u->assignRole('provider'));
        $hiddenProfile = Profile::factory([
            'user_id' => $provider->id,
            'provider_access_ends_at' => null,
            'is_complete' => true,
        ])->create();

        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('review.store', $hiddenProfile), [
            'rating' => 5,
            'comment' => 'Reviewing a hidden profile',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('reviews', ['profile_id' => $hiddenProfile->id]);
    }

    public function test_review_publishes_immediately(): void
    {
        Queue::fake();
        $profile = $this->makeVisibleProfile();
        $user = $this->makeUser();

        app(ReviewCreationService::class)->create($user, $profile, 4, 'Great service!');

        $review = Review::where('profile_id', $profile->id)->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(ReviewStatus::APPROVED, $review->status);
    }

    public function test_rating_stats_recalculation_is_dispatched_after_review(): void
    {
        Queue::fake();
        $profile = $this->makeVisibleProfile();
        $user = $this->makeUser();

        app(ReviewCreationService::class)->create($user, $profile, 5, 'Perfect!');

        Queue::assertPushed(RecalculateProfileStatsJob::class, function ($job) use ($profile) {
            return $job->profileId === $profile->id;
        });
    }

    public function test_daily_rate_limit_blocks_after_ten_reviews(): void
    {
        $user = $this->makeUser();

        Review::factory()->count(10)->for($user)->create(['created_at' => now()]);

        $profile = $this->makeVisibleProfile();

        $response = $this->actingAs($user)->post(route('review.store', $profile), [
            'rating' => 5,
            'comment' => 'Exceeding daily limit',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('reviews', ['profile_id' => $profile->id, 'user_id' => $user->id]);
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('review.store', $profile), [
            'rating' => 6,
            'comment' => 'Out of range rating',
        ]);

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseMissing('reviews', ['profile_id' => $profile->id, 'user_id' => $user->id]);
    }

    public function test_can_review_with_only_comment_and_null_rating(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('review.store', $profile), [
            'comment' => 'Only a text comment, no stars',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'profile_id' => $profile->id,
            'user_id' => $user->id,
            'rating' => null,
            'comment' => 'Only a text comment, no stars',
        ]);
    }

    public function test_can_review_with_only_rating_and_null_comment(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('review.store', $profile), [
            'rating' => 4,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'profile_id' => $profile->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => null,
        ]);
    }

    public function test_cannot_review_with_both_rating_and_comment_empty(): void
    {
        $profile = $this->makeVisibleProfile();
        $user = $this->makeUser();

        $response = $this->actingAs($user)->post(route('review.store', $profile), [
            'rating' => null,
            'comment' => '',
        ]);

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseMissing('reviews', ['profile_id' => $profile->id, 'user_id' => $user->id]);
    }
}
