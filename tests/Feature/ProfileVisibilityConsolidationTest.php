<?php

namespace Tests\Feature;

use App\Data\ProfileSearchFilters;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ProfileSearchService;
use App\Services\ProfileVisibilityService;
use App\Services\PublicFrontendService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileVisibilityConsolidationTest extends TestCase
{
    use RefreshDatabase;

    private ProfileVisibilityService $visibilityService;

    private ProfileSearchService $searchService;

    private PublicFrontendService $frontendService;

    private SubscriptionPlan $plan;

    private City $city;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->visibilityService = app(ProfileVisibilityService::class);
        $this->searchService = app(ProfileSearchService::class);
        $this->frontendService = app(PublicFrontendService::class);

        $this->plan = SubscriptionPlan::create([
            'name' => 'Monthly',
            'name_ar' => 'شهري',
            'duration_months' => 1,
            'price_lyd' => 50,
            'is_active' => true,
        ]);

        $this->city = City::create([
            'name' => 'Tripoli',
            'name_ar' => 'طرابلس',
            'slug' => 'tripoli',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Design',
            'name_ar' => 'تصميم',
            'slug' => 'design',
            'is_active' => true,
        ]);
    }

    public function test_visibility_service_single_profile_check_is_canonical(): void
    {
        $provider = $this->createActiveProvider();
        $profile = $provider->profile;

        // Canonical source of truth for single profile
        $this->assertTrue($this->visibilityService->isDiscoverable($profile));
        $this->assertTrue($profile->isDiscoverable());
    }

    public function test_query_scope_and_service_produce_same_visibility(): void
    {
        $provider = $this->createActiveProvider();
        $profile = $provider->profile;

        // All three methods should agree on visibility
        $singleCheck = $this->visibilityService->isDiscoverable($profile);
        $scopeCheck = Profile::join('users', 'users.id', '=', 'profiles.user_id')
            ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id')
            ->where('profiles.id', $profile->id)
            ->visible()
            ->exists();
        $queryCheck = $this->visibilityService->applyVisibleQuery(
            Profile::query()
                ->join('users', 'users.id', '=', 'profiles.user_id')
                ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id')
        )->where('profiles.id', $profile->id)->exists();

        $this->assertTrue($singleCheck);
        $this->assertTrue($scopeCheck);
        $this->assertTrue($queryCheck);
    }

    public function test_incomplete_profile_hidden_everywhere(): void
    {
        $provider = $this->createProvider();
        $profile = $provider->profile;
        $this->createActiveSubscription($provider);

        // Profile is incomplete
        $this->assertFalse($profile->is_complete);

        // Hidden by all methods
        $this->assertFalse($this->visibilityService->isDiscoverable($profile));
        $results = $this->searchService->search(new ProfileSearchFilters(
            page: 1,
            perPage: 15,
        ));
        $this->assertFalse($results->pluck('id')->contains($profile->id));
        $this->assertFalse($this->frontendService->homepage()['data']['featuredProviders']->pluck('id')->contains($profile->id));
    }

    public function test_suspended_provider_hidden_everywhere(): void
    {
        $provider = $this->createActiveProvider();
        $profile = $provider->profile;

        // Initially visible
        $this->assertTrue($this->visibilityService->isDiscoverable($profile));

        // Suspend provider
        $provider->update(['is_suspended' => true]);
        $provider->refresh();
        $profile->refresh();

        // Hidden by all methods
        $this->assertFalse($this->visibilityService->isDiscoverable($profile));
        $results = $this->searchService->search(new ProfileSearchFilters(
            page: 1,
            perPage: 15,
        ));
        $this->assertFalse($results->pluck('id')->contains($profile->id));
    }

    public function test_inactive_user_hidden_everywhere(): void
    {
        $provider = $this->createActiveProvider();
        $profile = $provider->profile;

        // Initially visible
        $this->assertTrue($this->visibilityService->isDiscoverable($profile));

        // Deactivate user
        $provider->update(['is_active' => false]);
        $provider->refresh();
        $profile->refresh();

        // Hidden by all methods
        $this->assertFalse($this->visibilityService->isDiscoverable($profile));
        $results = $this->searchService->search(new ProfileSearchFilters(
            page: 1,
            perPage: 15,
        ));
        $this->assertFalse($results->pluck('id')->contains($profile->id));
    }

    public function test_expired_subscription_hidden_everywhere(): void
    {
        $provider = $this->createActiveProvider();
        $profile = $provider->profile;

        // Expire the subscription
        $provider->subscriptions()->update(['ends_at' => Carbon::yesterday()->toDateString()]);

        // Hidden by all methods
        $this->assertFalse($this->visibilityService->isDiscoverable($profile));
        $this->assertFalse($this->searchService->search(new ProfileSearchFilters(
            page: 1,
            perPage: 15,
        ))->pluck('id')->contains($profile->id));
    }

    public function test_inactive_subscription_hidden_everywhere(): void
    {
        $provider = $this->createActiveProvider();
        $profile = $provider->profile;

        // Deactivate subscription
        $provider->subscriptions()->update(['is_active' => false]);

        // Hidden by all methods
        $this->assertFalse($this->visibilityService->isDiscoverable($profile));
        $this->assertFalse($this->searchService->search(new ProfileSearchFilters(
            page: 1,
            perPage: 15,
        ))->pluck('id')->contains($profile->id));
    }

    public function test_no_subscription_hidden_everywhere(): void
    {
        $provider = $this->createProvider();
        $provider->profile->update(['is_complete' => true]);

        // No subscription

        // Hidden by all methods
        $this->assertFalse($this->visibilityService->isDiscoverable($provider->profile));
        $this->assertFalse($this->searchService->search(new ProfileSearchFilters(
            page: 1,
            perPage: 15,
        ))->pluck('id')->contains($provider->profile->id));
    }

    public function test_active_subscription_visible_everywhere(): void
    {
        $provider = $this->createActiveProvider();
        $profile = $provider->profile;

        // Visible by all methods
        $this->assertTrue($this->visibilityService->isDiscoverable($profile));
        $searchResults = $this->searchService->search(new ProfileSearchFilters(
            page: 1,
            perPage: 15,
        ))->pluck('id');
        $this->assertTrue($searchResults->contains($profile->id));
    }

    public function test_renewed_subscription_restores_visibility(): void
    {
        $provider = $this->createActiveProvider();
        $profile = $provider->profile;

        // Initially visible
        $this->assertTrue($this->visibilityService->isDiscoverable($profile));

        // Expire subscription (set is_active to false)
        $oldSubscription = $provider->subscriptions()->first();
        $oldSubscription->update(['is_active' => false]);

        // Now hidden
        $this->assertFalse($this->visibilityService->isDiscoverable($profile));

        // Renew with new subscription (different dates to avoid overlap)
        Subscription::create([
            'user_id' => $provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => $oldSubscription->ends_at->addDay(),
            'ends_at' => $oldSubscription->ends_at->addDay()->addMonth(),
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => 1,
            'processed_at' => now(),
            'processed_by' => 1,
        ]);

        // Visible again
        $this->assertTrue($this->visibilityService->isDiscoverable($provider->profile->fresh()));
    }

    public function test_approved_at_null_does_not_hide_profile(): void
    {
        $provider = $this->createProvider();
        $provider->profile->update(['is_complete' => true]);

        // Create subscription with approved_at explicitly null (shouldn't hide profile)
        Subscription::create([
            'user_id' => $provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::today()->addMonth(),
            'is_active' => true,
            'approved_at' => null,
            'approved_by' => null,
            'processed_at' => now(),
            'processed_by' => 1,
        ]);

        // Should still be visible (approved_at is not checked)
        $this->assertTrue($this->visibilityService->isDiscoverable($provider->profile));
    }

    public function test_all_visibility_checks_use_same_rules(): void
    {
        // Create multiple providers with different states
        $completeActive = $this->createActiveProvider();
        $completeSuspended = $this->createActiveProvider();
        $completeSuspended->update(['is_suspended' => true]);
        $completeInactive = $this->createActiveProvider();
        $completeInactive->update(['is_active' => false]);

        // All three methods should agree on visibility for each
        foreach ([$completeActive, $completeSuspended, $completeInactive] as $provider) {
            $profile = $provider->profile()->first();
            $singleCheck = $this->visibilityService->isDiscoverable($profile);
            $searchCheck = $this->searchService->search(new ProfileSearchFilters(
                page: 1,
                perPage: 50,
            ))->pluck('id')->contains($profile->id);

            $this->assertEquals($singleCheck, $searchCheck, "Visibility mismatch for profile {$profile->id}");
        }
    }

    public function test_no_stale_approved_at_checks_in_visibility(): void
    {
        // Create a subscription without approved_at
        $provider = $this->createProvider();
        $provider->profile->update(['is_complete' => true]);

        Subscription::create([
            'user_id' => $provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::today()->addMonth(),
            'is_active' => true,
            'approved_at' => null,  // This should NOT hide the profile
        ]);

        // Profile should be visible
        $this->assertTrue($this->visibilityService->isDiscoverable($provider->profile));

        // Should appear in search
        $search = $this->searchService->search(new ProfileSearchFilters(
            page: 1,
            perPage: 50,
        ));
        $this->assertTrue($search->pluck('id')->contains($provider->profile->id));
    }

    protected function createProvider(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge(['is_active' => true, 'is_suspended' => false], $attributes));
        $user->assignRole('provider');

        // Ensure profile exists (observer might not fire in test context)
        if (! $user->profile) {
            Profile::create([
                'user_id' => $user->id,
                'slug' => 'provider-'.$user->id,
            ]);
            $user = $user->refresh();
        }

        $user->profile->update([
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'phone' => '+218123456789',
            'whatsapp' => '+218123456789',
            'is_complete' => false,
        ]);

        // Create stats if not already created
        if (! $user->profile->stats) {
            $user->profile->stats()->create([
                'rating_avg' => 0,
                'reviews_count' => 0,
                'is_top_rated' => false,
                'is_featured' => false,
                'is_homepage_featured' => false,
                'is_top_search' => false,
                'is_top_category' => false,
                'is_top_subcategory' => false,
            ]);
        }

        return $user->refresh();
    }

    private function createActiveProvider(): User
    {
        $provider = $this->createProvider();
        $provider->profile->update(['is_complete' => true]);
        $this->createActiveSubscription($provider);

        return $provider;
    }

    private function createActiveSubscription(User $provider): void
    {
        Subscription::create([
            'user_id' => $provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::today()->addMonth(),
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => 1,
            'processed_at' => now(),
            'processed_by' => 1,
        ]);
    }
}
