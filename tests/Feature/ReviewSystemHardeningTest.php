<?php

namespace Tests\Feature;

use App\Console\Commands\UpdateTopRatedProfilesCommand;
use App\Enums\ReviewStatus;
use App\Filament\Resources\ReviewResource;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ProfileStatsService;
use App\Services\ReviewModerationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReviewSystemHardeningTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private City $city;

    private SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->category = Category::create([
            'name' => 'Design',
            'name_ar' => 'Design',
            'slug' => 'design',
            'is_active' => true,
        ]);

        $this->city = City::create([
            'name' => 'Tripoli',
            'name_ar' => 'Tripoli',
            'slug' => 'tripoli',
            'is_active' => true,
        ]);

        $this->plan = SubscriptionPlan::create([
            'name' => 'Monthly',
            'name_ar' => 'Monthly',
            'duration_months' => 1,
            'price_lyd' => 50,
            'is_active' => true,
        ]);
    }

    public function test_review_routes_have_required_middleware_and_no_api_bypass_exists(): void
    {
        $storeMiddleware = Route::getRoutes()->getByName('review.store')->gatherMiddleware();
        $flagMiddleware = Route::getRoutes()->getByName('reviews.flag')->gatherMiddleware();

        foreach (['auth', 'account.locked', 'user.active', 'user.not_suspended'] as $middleware) {
            $this->assertContains($middleware, $storeMiddleware);
            $this->assertContains($middleware, $flagMiddleware);
        }

        $this->assertContains('review.eligible', $storeMiddleware);
        $this->assertContains('throttle:reviews.create', $storeMiddleware);
        $this->assertContains('throttle:reviews.flag', $flagMiddleware);
        $this->assertNull(Route::getRoutes()->getByName('api.reviews.store'));
        $this->assertNull(Route::getRoutes()->getByName('api.reviews.flag'));
    }

    public function test_only_eligible_public_users_can_create_live_reviews(): void
    {
        $profile = $this->discoverableProvider('eligible-provider');
        $reviewer = $this->user('user', ['created_at' => now()->subDays(2)]);

        $this->actingAs($reviewer)
            ->post(route('review.store', $profile), [
                'rating' => 5,
                'comment' => 'Excellent marketplace provider.',
            ])
            ->assertRedirect();

        $review = Review::first();

        $this->assertSame(ReviewStatus::APPROVED, $review->status);
        $this->assertSame(1, $profile->stats()->first()->reviews_count);
        $this->assertSame('5.0', (string) $profile->stats()->first()->rating_avg);
        $this->assertTrue($profile->fresh()->approvedReviews()->whereKey($review)->exists());
    }

    public function test_guest_provider_admin_suspended_and_new_users_cannot_create_reviews(): void
    {
        $profile = $this->discoverableProvider('blocked-provider');

        $this->post(route('review.store', $profile), ['rating' => 5])
            ->assertRedirect('/login');

        $this->actingAs($this->user('provider', ['created_at' => now()->subDays(2)]))
            ->post(route('review.store', $profile), ['rating' => 5])
            ->assertForbidden();

        $this->actingAs($this->user('super_admin', ['created_at' => now()->subDays(2)]))
            ->post(route('review.store', $profile), ['rating' => 5])
            ->assertForbidden();

        $this->actingAs($this->user('user', [
            'created_at' => now()->subDays(2),
            'is_suspended' => true,
        ]))
            ->post(route('review.store', $profile), ['rating' => 5])
            ->assertRedirect();

        $this->actingAs($this->user('user'))
            ->post(route('review.store', $profile), ['rating' => 5])
            ->assertRedirect();

        $this->assertSame(0, Review::count());
    }

    public function test_duplicate_self_review_and_daily_limit_are_blocked(): void
    {
        $profile = $this->discoverableProvider('duplicate-provider');
        $reviewer = $this->user('user', ['created_at' => now()->subDays(2)]);

        $this->actingAs($reviewer)
            ->post(route('review.store', $profile), ['rating' => 5])
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('review.store', $profile), ['rating' => 4])
            ->assertSessionHasErrors('profile');

        $owner = $this->user('user', ['created_at' => now()->subDays(2)]);
        $ownProfile = $this->profileFor($owner, 'self-review-profile');
        $this->activeSubscription($owner);

        $this->actingAs($owner)
            ->post(route('review.store', $ownProfile), ['rating' => 5])
            ->assertForbidden();

        $heavyReviewer = $this->user('user', ['created_at' => now()->subDays(2)]);
        for ($i = 0; $i < 10; $i++) {
            Review::withoutEvents(fn (): Review => Review::create([
                'profile_id' => $this->discoverableProvider('daily-limit-'.$i)->id,
                'user_id' => $heavyReviewer->id,
                'rating' => 5,
                'status' => ReviewStatus::APPROVED,
            ]));
        }

        $this->actingAs($heavyReviewer)
            ->post(route('review.store', $this->discoverableProvider('daily-limit-final')), ['rating' => 5])
            ->assertRedirect();
    }

    public function test_users_and_profile_owners_can_flag_reviews_without_hiding_them(): void
    {
        $profile = $this->discoverableProvider('flagged-provider');
        $reviewer = $this->user('user', ['created_at' => now()->subDays(2)]);
        $review = $this->approvedReview($profile, $reviewer, 5);
        $flagger = $this->user('user', ['created_at' => now()->subDays(2)]);

        $this->actingAs($flagger)
            ->post(route('reviews.flag', $review), ['reason' => 'This review looks suspicious.'])
            ->assertRedirect();

        $review->refresh();
        $this->assertTrue($review->is_flagged);
        $this->assertSame($flagger->id, $review->flagged_by);
        $this->assertNull($review->flag_handled_at);
        $this->assertTrue($profile->fresh()->approvedReviews()->whereKey($review)->exists());

        $providerOwner = $profile->user;
        $review->update(['is_flagged' => false, 'flagged_by' => null, 'flagged_at' => null, 'flagged_reason' => null]);

        $this->actingAs($providerOwner)
            ->post(route('reviews.flag', $review), ['reason' => 'Provider disputes this review.'])
            ->assertRedirect();

        $this->assertTrue($review->refresh()->is_flagged);
    }

    public function test_review_flagging_rejects_own_review_and_suspended_users(): void
    {
        $profile = $this->discoverableProvider('flag-block-provider');
        $reviewer = $this->user('user', ['created_at' => now()->subDays(2)]);
        $review = $this->approvedReview($profile, $reviewer, 5);

        $this->actingAs($reviewer)
            ->post(route('reviews.flag', $review), ['reason' => 'Trying to flag my own review.'])
            ->assertForbidden();

        $this->actingAs($this->user('user', [
            'created_at' => now()->subDays(2),
            'is_suspended' => true,
        ]))
            ->post(route('reviews.flag', $review), ['reason' => 'Suspended user flag attempt.'])
            ->assertRedirect();
    }

    public function test_admin_can_inspect_flagged_reviews_and_mark_flags_handled(): void
    {
        $admin = $this->user('super_admin', ['created_at' => now()->subDays(2)]);
        $profile = $this->discoverableProvider('admin-flag-provider');
        $review = $this->approvedReview($profile, $this->user('user', ['created_at' => now()->subDays(2)]), 5);
        $review->update([
            'is_flagged' => true,
            'flagged_by' => $admin->id,
            'flagged_at' => now(),
            'flagged_reason' => 'Needs admin inspection.',
        ]);

        $this->actingAs($admin);

        $this->assertTrue(ReviewResource::getEloquentQuery()->whereKey($review->id)->exists());

        app(ReviewModerationService::class)->markFlagHandled($review);

        $this->assertNotNull($review->refresh()->flag_handled_at);
        $this->assertSame($admin->id, $review->flag_handled_by);
    }

    public function test_review_flag_handled_columns_exist_and_are_mass_assignable(): void
    {
        $this->assertTrue(Schema::hasColumn('reviews', 'flag_handled_at'));
        $this->assertTrue(Schema::hasColumn('reviews', 'flag_handled_by'));

        $admin = $this->user('super_admin', ['created_at' => now()->subDays(2)]);
        $profile = $this->discoverableProvider('flag-schema-provider');
        $review = $this->approvedReview($profile, $this->user('user', ['created_at' => now()->subDays(2)]), 5);

        $review->update([
            'is_flagged' => true,
            'flagged_by' => $admin->id,
            'flagged_at' => now(),
            'flagged_reason' => 'Schema regression coverage.',
            'flag_handled_at' => now(),
            'flag_handled_by' => $admin->id,
        ]);

        $review->refresh();

        $this->assertNotNull($review->flag_handled_at);
        $this->assertSame($admin->id, $review->flagHandledBy->id);
    }

    public function test_soft_deleted_and_rejected_reviews_are_excluded_from_stats_and_top_rated_command(): void
    {
        $profile = $this->discoverableProvider('ranking-provider');

        for ($i = 0; $i < 5; $i++) {
            $this->approvedReview($profile, $this->user('user', ['created_at' => now()->subDays(2)]), 5);
        }

        app(ProfileStatsService::class)->recalculate($profile);
        $this->assertTrue($profile->stats()->first()->is_top_rated);

        $profile->reviews()->first()->update(['status' => ReviewStatus::REJECTED]);
        $profile->reviews()->skip(1)->first()->delete();

        $stats = $profile->stats()->first();
        $this->assertSame(3, $stats->reviews_count);
        $this->assertFalse($stats->is_top_rated);

        $profile->stats()->update(['is_top_rated' => true]);
        Artisan::call(UpdateTopRatedProfilesCommand::class);

        $this->assertFalse($profile->stats()->first()->is_top_rated);
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::withoutEvents(fn (): User => User::factory()->create(array_merge([
            'is_active' => true,
            'is_suspended' => false,
        ], $attributes)));

        $user->assignRole($role);

        return $user;
    }

    private function discoverableProvider(string $slug): Profile
    {
        $provider = $this->user('provider', ['created_at' => now()->subDays(2)]);
        $profile = $this->profileFor($provider, $slug);
        $this->activeSubscription($provider);

        return $profile;
    }

    private function profileFor(User $user, string $slug): Profile
    {
        $profile = Profile::withoutEvents(fn (): Profile => Profile::create([
            'user_id' => $user->id,
            'business_name' => 'Provider '.$slug,
            'bio' => 'Provider bio for review hardening tests.',
            'slug' => $slug,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'whatsapp' => '+218911234567',
            'phone' => '+218911234567',
            'is_complete' => true,
        ]));

        $profile->stats()->create([
            'rating_avg' => 0,
            'reviews_count' => 0,
            'is_top_rated' => false,
            'is_featured' => false,
            'is_homepage_featured' => false,
            'is_top_search' => false,
            'is_top_category' => false,
            'is_top_subcategory' => false,
        ]);

        return $profile;
    }

    private function activeSubscription(User $user): void
    {
        Subscription::withoutEvents(fn (): Subscription => Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->subDay()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
            'approved_at' => now(),
        ]));
    }

    private function approvedReview(Profile $profile, User $reviewer, int $rating): Review
    {
        return Review::create([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'rating' => $rating,
            'status' => ReviewStatus::APPROVED,
        ]);
    }
}
