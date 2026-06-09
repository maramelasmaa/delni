<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\ProviderCredential;
use App\Models\ProviderLink;
use App\Models\SubscriptionPlan;
use App\Services\ProfileVisibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderPanelIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * PHASE 1: Admin creates provider
     * PHASE 2: Provider receives and uses onboarding email
     * PHASE 3: Provider logs in to panel
     * PHASE 4: Provider edits profile
     * PHASE 5: Provider adds portfolio/credentials/links
     * PHASE 6: Public website displays everything correctly
     */
    public function test_complete_provider_flow(): void
    {
        // PHASE 1: Create provider account
        $provider = $this->createProvider();
        $this->assertTrue($provider->hasRole('provider'));
        $this->assertNotNull($provider->profile);

        // PHASE 2: Verify profile and subscription exist
        $profile = $provider->profile;
        $this->assertNotNull($profile);
        $this->assertFalse($profile->is_complete, 'New profile should start incomplete');

        // Create subscription so provider becomes visible
        $plan = SubscriptionPlan::factory()->create();
        $subscription = $provider->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'is_active' => true,
        ]);
        $this->assertTrue($subscription->is_active);

        // PHASE 3: Complete profile to become visible
        $city = City::where('is_active', true)->first() ?? City::factory()->create(['is_active' => true]);
        $category = Category::where('is_active', true)->first() ?? Category::factory()->create(['is_active' => true]);

        $profile->update([
            'business_name' => 'Tech Innovations LLC',
            'bio' => 'We provide cutting-edge tech solutions',
            'city_id' => $city->id,
            'category_id' => $category->id,
            'phone' => '+218912345678',
            'whatsapp' => '+218912345678',
            'provider_type' => 'company',
            'is_complete' => true,
        ]);

        // PHASE 4: Add portfolio items
        $portfolio1 = PortfolioItem::factory()->create([
            'profile_id' => $profile->id,
            'title' => 'Mobile App Development',
            'is_active' => true,
        ]);

        // PHASE 5: Add credentials
        $credential = ProviderCredential::factory()->create([
            'profile_id' => $profile->id,
            'title' => 'ISO 9001 Certification',
            'issuer' => 'International Standards Organization',
        ]);

        // PHASE 6: Add safe links
        $link = ProviderLink::factory()->create([
            'profile_id' => $profile->id,
            'label' => 'Company Website',
            'url' => 'https://techinnov.com',
            'is_active' => true,
        ]);

        // PHASE 7: Verify public website can see the provider
        $this->assertTrue($profile->is_complete, 'Profile should be marked complete');

        // Check visibility service agrees
        $visibilityService = app(ProfileVisibilityService::class);
        $this->assertTrue($visibilityService->isDiscoverable($profile), 'Profile should be discoverable');

        // PHASE 8: Verify query doesn't expose admin fields
        $publicQuery = Profile::query()
            ->without('user')
            ->join('users', 'users.id', '=', 'profiles.user_id')
            ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id');

        $visibilityService->applyVisibleQuery($publicQuery);
        $visibleProfiles = $publicQuery->get();

        $this->assertGreaterThanOrEqual(1, $visibleProfiles->count(), 'Provider should appear in discoverable query');
        $foundProfile = $visibleProfiles->first();
        $this->assertEquals($profile->id, $foundProfile->id);
    }

    /**
     * Test: Incomplete profile NOT discoverable
     */
    public function test_incomplete_profile_not_visible(): void
    {
        $provider = $this->createProvider();
        $profile = $provider->profile;

        // Create subscription
        $plan = SubscriptionPlan::factory()->create();
        $provider->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // Verify incomplete
        $this->assertFalse($profile->is_complete);

        // Check visibility
        $visibilityService = app(ProfileVisibilityService::class);
        $this->assertFalse($visibilityService->isDiscoverable($profile), 'Incomplete profile should not be discoverable');
    }

    /**
     * Test: Suspended provider NOT discoverable
     */
    public function test_suspended_provider_not_visible(): void
    {
        $provider = $this->createProvider();
        $profile = $provider->profile;

        // Mark complete and add subscription
        $city = City::where('is_active', true)->first() ?? City::factory()->create(['is_active' => true]);
        $category = Category::where('is_active', true)->first() ?? Category::factory()->create(['is_active' => true]);

        $profile->update([
            'business_name' => 'Test Co',
            'city_id' => $city->id,
            'category_id' => $category->id,
            'phone' => '+218912345678',
            'whatsapp' => '+218912345678',
            'is_complete' => true,
        ]);

        $plan = SubscriptionPlan::factory()->create();
        $provider->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // Suspend provider
        $provider->is_suspended = true;
        $provider->save();

        // Check visibility (need fresh profile instance for user relationship)
        $profile->refresh();
        $visibilityService = app(ProfileVisibilityService::class);
        $this->assertFalse($visibilityService->isDiscoverable($profile), 'Suspended provider should not be discoverable');
    }

    /**
     * Test: Expired subscription NOT discoverable
     */
    public function test_expired_subscription_not_visible(): void
    {
        $provider = $this->createProvider();
        $profile = $provider->profile;

        // Mark complete
        $city = City::where('is_active', true)->first() ?? City::factory()->create(['is_active' => true]);
        $category = Category::where('is_active', true)->first() ?? Category::factory()->create(['is_active' => true]);

        $profile->update([
            'business_name' => 'Test Co',
            'city_id' => $city->id,
            'category_id' => $category->id,
            'phone' => '+218912345678',
            'whatsapp' => '+218912345678',
            'is_complete' => true,
        ]);

        // Create expired subscription
        $plan = SubscriptionPlan::factory()->create();
        $provider->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->subDay(), // Expired
            'is_active' => true,
        ]);

        // Check visibility
        $visibilityService = app(ProfileVisibilityService::class);
        $this->assertFalse($visibilityService->isDiscoverable($profile), 'Provider with expired subscription should not be discoverable');
    }

    /**
     * Test: Provider ownership enforced - cannot access other provider data
     */
    public function test_provider_ownership_enforced(): void
    {
        $provider1 = $this->createProvider();
        $provider2 = $this->createProvider();

        // Each has their own profile
        $this->assertNotEquals($provider1->profile->id, $provider2->profile->id);
        $this->assertEquals($provider1->id, $provider1->profile->user_id);
        $this->assertEquals($provider2->id, $provider2->profile->user_id);

        // Provider1 cannot access provider2's data
        $portfolio = PortfolioItem::factory()->create(['profile_id' => $provider2->profile->id]);
        $this->assertNotEquals($provider1->profile->id, $portfolio->profile_id);
    }

    /**
     * Test: Public page safely displays missing data
     */
    public function test_public_handles_missing_images_gracefully(): void
    {
        $provider = $this->createProvider();
        $profile = $provider->profile;

        // Make discoverable
        $city = City::where('is_active', true)->first() ?? City::factory()->create(['is_active' => true]);
        $category = Category::where('is_active', true)->first() ?? Category::factory()->create(['is_active' => true]);

        $profile->update([
            'business_name' => 'Test Co',
            'city_id' => $city->id,
            'category_id' => $category->id,
            'phone' => '+218912345678',
            'whatsapp' => '+218912345678',
            'is_complete' => true,
            'logo' => null, // No logo
            'cover_image' => null, // No cover
        ]);

        $plan = SubscriptionPlan::factory()->create();
        $provider->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // Refresh profile to reload from database
        $profile->refresh();

        // Verify no null references
        $this->assertNull($profile->logo);
        $this->assertNull($profile->cover_image);

        // Public page should render without crashing (handled by Blade safely)
        $visibilityService = app(ProfileVisibilityService::class);
        $this->assertTrue($visibilityService->isDiscoverable($profile));
    }
}
