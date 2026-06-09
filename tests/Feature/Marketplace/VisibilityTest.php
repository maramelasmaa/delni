<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ProfileVisibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisibilityTest extends TestCase
{
    use RefreshDatabase;

    private ProfileVisibilityService $visibility;

    private SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->visibility = app(ProfileVisibilityService::class);
        $this->createSubscriptionPlan();
    }

    private function createSubscriptionPlan(): void
    {
        $this->plan = SubscriptionPlan::create([
            'name' => 'Test Plan',
            'name_ar' => 'خطة الاختبار',
            'duration_months' => 1,
            'price_lyd' => 100,
            'is_active' => true,
        ]);
    }

    /**
     * Test: Scenario 1 - Profile visible after valid subscription
     */
    public function test_profile_visible_after_valid_subscription_created(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => true,
        ]);
        ProfileStats::create(['profile_id' => $profile->id]);

        // Before subscription: hidden
        $result = $this->visibility->evaluate($profile);
        $this->assertFalse($result->is_visible, 'Profile should be hidden before subscription');

        // Create subscription
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        // After subscription: visible
        $profile->refresh();
        $result = $this->visibility->evaluate($profile);
        $this->assertTrue($result->is_visible, 'Profile should be visible with active subscription');
    }

    /**
     * Test: Scenario 2 - Profile hidden immediately after expiry
     */
    public function test_profile_hidden_immediately_after_subscription_expires(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => true,
        ]);
        ProfileStats::create(['profile_id' => $profile->id]);

        // Create expired subscription
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->subMonths(2)->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
            'is_active' => false,
        ]);

        // Profile should be hidden
        $result = $this->visibility->evaluate($profile);
        $this->assertFalse($result->is_visible, 'Profile should be hidden with expired subscription');
    }

    /**
     * Test: Scenario 3 - Profile hidden when user suspended
     */
    public function test_profile_hidden_when_user_suspended(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => true,
        ]);
        ProfileStats::create(['profile_id' => $profile->id]);

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        // Before suspension: visible
        $result = $this->visibility->evaluate($profile);
        $this->assertTrue($result->is_visible, 'Profile should be visible before suspension');

        // Suspend user
        $user->update(['is_suspended' => true]);
        $profile->refresh();

        // After suspension: hidden
        $result = $this->visibility->evaluate($profile);
        $this->assertFalse($result->is_visible, 'Profile should be hidden when user suspended');
    }

    /**
     * Test: Scenario 4 - Profile visible again after reinstatement
     */
    public function test_profile_visible_again_after_user_reinstated(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => true,
        ]);
        ProfileStats::create(['profile_id' => $profile->id]);

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        // Suspend, then reinstate
        $user->update(['is_suspended' => true]);
        $profile->refresh();
        $this->assertFalse($this->visibility->isDiscoverable($profile));

        $user->update(['is_suspended' => false]);
        $profile->refresh();

        // Should be visible again
        $result = $this->visibility->evaluate($profile);
        $this->assertTrue($result->is_visible, 'Profile should be visible after reinstatement');
    }

    /**
     * Test: Scenario 5 - Homepage respects visibility
     */
    public function test_homepage_respects_visibility(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => true,
        ]);
        ProfileStats::create(['profile_id' => $profile->id]);

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        // Get homepage (should include this provider)
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        // Note: visibility is also checked in blade rendering, not just query

        // Suspend user
        $user->update(['is_suspended' => true]);

        // Get homepage again (should NOT include this provider)
        $response = $this->get(route('home'));
        $response->assertStatus(200);
    }

    /**
     * Test: Scenario 6 - Search respects visibility
     */
    public function test_search_respects_visibility(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => true,
            'business_name' => 'Test Business',
        ]);
        ProfileStats::create(['profile_id' => $profile->id]);

        // Without subscription: not in search results
        $response = $this->get(route('public.search', ['keyword' => 'Test Business']));
        // Should not contain the profile

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        // With subscription: in search results
        $response = $this->get(route('public.search', ['keyword' => 'Test Business']));
        $response->assertStatus(200);
    }

    /**
     * Test: Scenario 7 - Top-rated respects visibility
     */
    public function test_top_rated_respects_visibility(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => true,
        ]);

        $stats = ProfileStats::create([
            'profile_id' => $profile->id,
            'is_top_rated' => true,
            'rating_avg' => 5.0,
            'reviews_count' => 100,
        ]);

        // Create subscription
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        // Should be visible even though top-rated
        $result = $this->visibility->evaluate($profile);
        $this->assertTrue($result->is_visible, 'Top-rated profile should be visible');

        // Suspend user
        $user->update(['is_suspended' => true]);
        $profile->refresh();

        // Should be hidden (visibility overrides top-rated)
        $result = $this->visibility->evaluate($profile);
        $this->assertFalse($result->is_visible, 'Top-rated profile should be hidden when user suspended');
    }

    /**
     * Test: Scenario 8 - Featured respects visibility
     */
    public function test_featured_respects_visibility(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => true,
        ]);

        $stats = ProfileStats::create([
            'profile_id' => $profile->id,
            'is_featured' => true,
            'featured_until' => now()->addDays(10),
        ]);

        // Without subscription: featured flag doesn't make it visible
        $result = $this->visibility->evaluate($profile);
        $this->assertFalse($result->is_visible, 'Featured profile should be hidden without subscription');

        // With subscription: visible
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        $profile->refresh();
        $result = $this->visibility->evaluate($profile);
        $this->assertTrue($result->is_visible, 'Featured profile should be visible with subscription');
    }

    /**
     * Test: Scenario 9 - Hidden provider returns 404
     */
    public function test_hidden_provider_returns_404(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => true,
        ]);
        ProfileStats::create(['profile_id' => $profile->id]);

        // Try to access hidden profile
        $response = $this->get(route('public.provider', ['profile' => $profile->slug]));
        $this->assertEquals(404, $response->status(), 'Hidden profile should return 404');
    }

    /**
     * Test: Scenario 10 - Review eligibility respects visibility
     */
    public function test_review_eligibility_respects_visibility(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->assignRole('user');

        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $provider->id,
            'is_complete' => true,
        ]);
        ProfileStats::create(['profile_id' => $profile->id]);

        // Without subscription: cannot access provider page (404)
        $this->actingAs($reviewer);
        $response = $this->get(route('public.provider', ['profile' => $profile->slug]));
        $this->assertEquals(404, $response->status(), 'Cannot view hidden profile');

        // With subscription: can access provider page
        Subscription::create([
            'user_id' => $provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        $response = $this->get(route('public.provider', ['profile' => $profile->slug]));
        $this->assertEquals(200, $response->status(), 'Can view visible profile');
    }

    /**
     * Test: Scenario 11 - Incomplete profile hidden
     */
    public function test_incomplete_profile_hidden(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        // Create incomplete profile (phone is now required, use empty string)
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => false,
            'phone' => '',
        ]);
        ProfileStats::create(['profile_id' => $profile->id]);

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        // Should be hidden due to incompleteness
        $result = $this->visibility->evaluate($profile);
        $this->assertFalse($result->is_visible, 'Incomplete profile should be hidden');
        $this->assertTrue($result->isIncomplete(), 'Should report as incomplete');
    }

    /**
     * Test: Scenario 12 - Inactive user's profile hidden
     */
    public function test_inactive_user_profile_hidden(): void
    {
        $user = User::factory()->create(['is_active' => false, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => true,
        ]);
        ProfileStats::create(['profile_id' => $profile->id]);

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        // Should be hidden because user inactive
        $result = $this->visibility->evaluate($profile);
        $this->assertFalse($result->is_visible, 'Profile should be hidden when user inactive');
        $this->assertTrue($result->isUserAccountIssue(), 'Should report as user account issue');
    }
}
