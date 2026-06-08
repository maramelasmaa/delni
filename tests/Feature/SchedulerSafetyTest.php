<?php

namespace Tests\Feature;

use App\Data\ProfileSearchFilters;
use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\Subcategory;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ProfileSearchService;
use App\Services\ProfileVisibilityService;
use App\Services\PublicFrontendService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SchedulerSafetyTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionPlan $plan;

    private City $city;

    private Category $category;

    private Subcategory $subcategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->plan = SubscriptionPlan::create([
            'name' => 'Safety Plan',
            'name_ar' => 'Safety Plan',
            'duration_months' => 1,
            'price_lyd' => 100,
            'is_active' => true,
        ]);
        $this->city = City::create(['name' => 'Tripoli', 'name_ar' => 'Tripoli', 'slug' => 'tripoli', 'is_active' => true]);
        $this->category = Category::create(['name' => 'Design', 'name_ar' => 'Design', 'slug' => 'design', 'is_active' => true]);
        $this->subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => 'Logo',
            'name_ar' => 'Logo',
            'slug' => 'logo',
            'is_active' => true,
        ]);
    }

    public function test_expired_active_subscription_is_hidden_without_scheduler_cleanup(): void
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider, ['slug' => 'expired-provider']);
        $this->subscription($provider, now()->subMonths(2), now()->subDay(), ['is_active' => true, 'approved_at' => now()->subMonth()]);

        $this->assertTrue($provider->subscriptions()->first()->is_active);
        $this->assertFalse(app(ProfileVisibilityService::class)->isDiscoverable($profile));

        $this->get('/provider/'.$profile->slug)->assertNotFound();

        $results = app(ProfileSearchService::class)
            ->search(new ProfileSearchFilters(perPage: 10))
            ->getCollection();

        $this->assertFalse($results->contains('id', $profile->id));
    }

    public function test_renewed_subscription_restores_visibility_without_scheduler_cleanup(): void
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider, ['slug' => 'renewed-provider']);
        $this->subscription($provider, now()->subMonths(2), now()->subDay(), ['is_active' => true, 'approved_at' => now()->subMonth()]);

        $this->assertFalse(app(ProfileVisibilityService::class)->isDiscoverable($profile));

        $this->subscription($provider, now(), now()->addMonth(), ['is_active' => true, 'approved_at' => now()]);

        $this->assertTrue(app(ProfileVisibilityService::class)->isDiscoverable($profile->refresh()));
        $this->get('/provider/'.$profile->slug)->assertOk();
    }

    public function test_expired_marketplace_placement_flag_does_not_boost_public_search(): void
    {
        $expiredPlacement = $this->discoverableProvider('expired-placement');
        $normal = $this->discoverableProvider('normal-provider');

        $expiredPlacement->stats()->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => now()->subDay(),
            'is_top_search' => true,
            'top_search_until' => now()->subDay(),
        ]);
        $normal->reviews()->createMany($this->reviewPayloads(5, 5));

        $results = app(ProfileSearchService::class)
            ->search(new ProfileSearchFilters(perPage: 10))
            ->getCollection();

        $this->assertSame($normal->id, $results->first()->id);
        $this->assertTrue($results->contains('id', $expiredPlacement->id));
    }

    public function test_top_rated_public_sections_use_live_approved_reviews_not_stale_precomputed_flag(): void
    {
        $staleFlag = $this->discoverableProvider('stale-top-rated');
        $liveTopRated = $this->discoverableProvider('live-top-rated');

        $staleFlag->stats()->update(['is_top_rated' => true, 'rating_avg' => 5, 'reviews_count' => 5]);
        $liveTopRated->stats()->update(['is_top_rated' => false, 'rating_avg' => 0, 'reviews_count' => 0]);
        $liveTopRated->reviews()->createMany($this->reviewPayloads(5, 5));

        $searchResults = app(ProfileSearchService::class)
            ->search(new ProfileSearchFilters(perPage: 10))
            ->getCollection();

        $this->assertSame($liveTopRated->id, $searchResults->first()->id);

        $homepageData = app(PublicFrontendService::class)->homepage()['data'];

        $this->assertTrue($homepageData['topRatedProviders']->contains('id', $liveTopRated->id));
        $this->assertFalse($homepageData['topRatedProviders']->contains('id', $staleFlag->id));
    }

    public function test_scheduler_health_check_reports_fresh_and_stale_scheduler_state(): void
    {
        Cache::put('scheduler:last_heartbeat_at', now()->toIso8601String(), now()->addDay());
        Cache::put('scheduler:subscriptions_expire:last_success_at', now()->toIso8601String(), now()->addDay());
        Cache::put('scheduler:placements_expire:last_success_at', now()->toIso8601String(), now()->addDay());
        Cache::put('scheduler:top_rated:last_success_at', now()->toIso8601String(), now()->addDay());

        $this->artisan('scheduler:health-check')
            ->assertSuccessful();

        Cache::put('scheduler:last_heartbeat_at', now()->subMinutes(10)->toIso8601String(), now()->addDay());

        $this->artisan('scheduler:health-check --max-heartbeat-age=60')
            ->assertFailed();
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'password' => Hash::make('Password123!'),
            'is_active' => true,
            'is_suspended' => false,

        ], $attributes));

        $user->assignRole($role);

        return $user;
    }

    private function profile(User $user, array $attributes = []): Profile
    {
        $profile = Profile::create(array_merge([
            'user_id' => $user->id,
            'business_name' => 'Business '.$user->id,
            'bio' => 'Useful provider bio',
            'slug' => 'business-'.$user->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'whatsapp' => '+218911234567',
            'phone' => '+218911234567',
            'is_complete' => true,
        ], $attributes));

        $profile->subcategories()->sync([$this->subcategory->id]);
        $profile->stats()->updateOrCreate([], [
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

    private function discoverableProvider(string $slug): Profile
    {
        $provider = $this->user('provider', ['email' => $slug.'@example.test']);
        $profile = $this->profile($provider, [
            'business_name' => 'Provider '.$slug,
            'slug' => $slug,
        ]);
        $this->subscription($provider, now(), now()->addMonth(), ['is_active' => true, 'approved_at' => now()]);

        return $profile;
    }

    private function subscription(User $user, mixed $startsAt, mixed $endsAt, array $attributes = []): Subscription
    {
        return Subscription::create(array_merge([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_active' => false,
        ], $attributes));
    }

    /** @return array<int, array<string, mixed>> */
    private function reviewPayloads(int $count, int $rating): array
    {
        return collect(range(1, $count))
            ->map(fn (int $index): array => [
                'user_id' => $this->user('user', ['email' => 'reviewer-'.$index.'-'.uniqid().'@example.test'])->id,
                'rating' => $rating,
                'status' => ReviewStatus::APPROVED,
                'comment' => 'Approved review '.$index,
            ])
            ->all();
    }
}
