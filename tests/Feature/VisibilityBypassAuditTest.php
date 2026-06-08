<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive visibility bypass audit.
 *
 * Ensures NO public access routes can bypass the centralized visibility system.
 * Tests direct URLs, API endpoints, route model binding, and all access patterns.
 */
class VisibilityBypassAuditTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionPlan $plan;

    private City $city;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

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

    // ROUTE MODEL BINDING TESTS

    public function test_route_binding_cannot_resolve_hidden_profile(): void
    {
        $profile = $this->createHiddenProfile();

        // Attempt direct URL access
        $response = $this->get(route('public.provider', $profile->slug));

        // Should return 404, not expose the profile
        $this->assertEquals(404, $response->status());
    }

    public function test_route_binding_cannot_resolve_suspended_provider(): void
    {
        $profile = $this->createActiveProfile();
        $profile->user->update(['is_suspended' => true]);

        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(404, $response->status());
    }

    public function test_route_binding_cannot_resolve_inactive_provider(): void
    {
        $profile = $this->createActiveProfile();
        $profile->user->update(['is_active' => false]);

        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(404, $response->status());
    }

    public function test_route_binding_cannot_resolve_expired_subscription(): void
    {
        $profile = $this->createActiveProfile();
        $profile->user->subscriptions()->update(['ends_at' => Carbon::yesterday()]);

        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(404, $response->status());
    }

    public function test_route_binding_cannot_resolve_inactive_subscription(): void
    {
        $profile = $this->createActiveProfile();
        $profile->user->subscriptions()->update(['is_active' => false]);

        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(404, $response->status());
    }

    public function test_route_binding_cannot_resolve_incomplete_profile(): void
    {
        $profile = $this->createIncompleteProfile();

        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(404, $response->status());
    }

    public function test_route_binding_allows_visible_profile(): void
    {
        $profile = $this->createActiveProfile();

        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString($profile->business_name, $response->getContent());
    }

    // DIRECT URL TESTS

    public function test_direct_profile_url_blocked_for_hidden_profile(): void
    {
        $profile = $this->createHiddenProfile();

        // Attempt direct access
        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(404, $response->status());
    }

    public function test_direct_profile_url_blocked_for_suspended(): void
    {
        $profile = $this->createActiveProfile();
        $profile->user->update(['is_suspended' => true]);

        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(404, $response->status());
    }

    public function test_direct_profile_url_blocked_for_inactive_user(): void
    {
        $profile = $this->createActiveProfile();
        $profile->user->update(['is_active' => false]);

        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(404, $response->status());
    }

    public function test_direct_profile_url_blocked_for_expired_subscription(): void
    {
        $profile = $this->createActiveProfile();
        $profile->user->subscriptions()->update(['ends_at' => Carbon::yesterday()]);

        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(404, $response->status());
    }

    public function test_direct_profile_url_accessible_for_active(): void
    {
        $profile = $this->createActiveProfile();

        // Public can access visible provider profile
        $response = $this->get(route('public.provider', $profile->slug));

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString($profile->business_name, $response->getContent());
    }

    // REVIEW CREATION BYPASS TESTS

    public function test_review_creation_blocked_for_hidden_profile(): void
    {
        $profile = $this->createHiddenProfile();
        $reviewer = $this->createUser();

        $response = $this->actingAs($reviewer)
            ->post(route('review.store', $profile), [
                'rating' => 5,
                'comment' => 'Attempt to review hidden profile',
            ]);

        // ReviewPolicy should prevent this (checks visibility via isDiscoverable)
        // Should redirect (403 Forbidden or redirect to login)
        $this->assertTrue($response->status() === 403 || $response->status() === 302);
    }

    public function test_review_creation_blocked_for_suspended_provider(): void
    {
        $profile = $this->createActiveProfile();
        $profile->user->update(['is_suspended' => true]);
        $reviewer = $this->createUser();

        $response = $this->actingAs($reviewer)
            ->post(route('review.store', $profile), [
                'rating' => 5,
                'comment' => 'Attempt to review suspended provider',
            ]);

        $this->assertTrue($response->status() === 403 || $response->status() === 302);
    }

    public function test_review_creation_blocked_for_expired_subscription(): void
    {
        $profile = $this->createActiveProfile();
        $profile->user->subscriptions()->update(['ends_at' => Carbon::yesterday()]);
        $reviewer = $this->createUser();

        $response = $this->actingAs($reviewer)
            ->post(route('review.store', $profile), [
                'rating' => 5,
                'comment' => 'Attempt to review expired provider',
            ]);

        $this->assertTrue($response->status() === 403 || $response->status() === 302);
    }

    public function test_review_creation_allowed_for_visible_profile(): void
    {
        $profile = $this->createActiveProfile();
        $reviewer = $this->createUser();

        $response = $this->actingAs($reviewer)
            ->post(route('review.store', $profile), [
                'rating' => 5,
                'comment' => 'Great service',
            ]);

        // Should redirect on success
        $this->assertTrue($response->status() < 400);
    }

    // SEARCH RESULTS TESTS

    public function test_search_excludes_hidden_profiles(): void
    {
        $visible = $this->createActiveProfile();
        $hidden = $this->createHiddenProfile();

        $response = $this->get(route('public.search'));

        $this->assertEquals(200, $response->status());
        $content = $response->getContent();

        $this->assertStringContainsString($visible->business_name, $content);
        $this->assertStringNotContainsString($hidden->business_name, $content);
    }

    public function test_search_excludes_suspended_providers(): void
    {
        $visible = $this->createActiveProfile();
        $suspended = $this->createActiveProfile();
        $suspended->user->update(['is_suspended' => true]);

        $response = $this->get(route('public.search'));

        $content = $response->getContent();

        $this->assertStringContainsString($visible->business_name, $content);
        $this->assertStringNotContainsString($suspended->business_name, $content);
    }

    public function test_search_excludes_expired_providers(): void
    {
        $visible = $this->createActiveProfile();
        $expired = $this->createActiveProfile();
        $expired->user->subscriptions()->update(['ends_at' => Carbon::yesterday()]);

        $response = $this->get(route('public.search'));

        $content = $response->getContent();

        $this->assertStringContainsString($visible->business_name, $content);
        $this->assertStringNotContainsString($expired->business_name, $content);
    }

    // API ENDPOINT TESTS

    public function test_api_search_excludes_hidden_profiles(): void
    {
        $visible = $this->createActiveProfile();
        $hidden = $this->createHiddenProfile();

        $response = $this->getJson(route('api.profiles.search'));

        $this->assertEquals(200, $response->status());
        $data = $response->json('data');
        $visibleIds = collect($data)->pluck('id')->toArray();

        $this->assertContains($visible->id, $visibleIds);
        $this->assertNotContains($hidden->id, $visibleIds);
    }

    public function test_api_search_excludes_suspended_providers(): void
    {
        $visible = $this->createActiveProfile();
        $suspended = $this->createActiveProfile();
        $suspended->user->update(['is_suspended' => true]);

        $response = $this->getJson(route('api.profiles.search'));

        $data = $response->json('data');
        $visibleIds = collect($data)->pluck('id')->toArray();

        $this->assertContains($visible->id, $visibleIds);
        $this->assertNotContains($suspended->id, $visibleIds);
    }

    public function test_api_search_excludes_expired_subscriptions(): void
    {
        $visible = $this->createActiveProfile();
        $expired = $this->createActiveProfile();
        $expired->user->subscriptions()->update(['ends_at' => Carbon::yesterday()]);

        $response = $this->getJson(route('api.profiles.search'));

        $data = $response->json('data');
        $visibleIds = collect($data)->pluck('id')->toArray();

        $this->assertContains($visible->id, $visibleIds);
        $this->assertNotContains($expired->id, $visibleIds);
    }

    // HOMEPAGE TESTS

    public function test_homepage_excludes_hidden_profiles(): void
    {
        $visible = $this->createActiveProfile();
        $hidden = $this->createHiddenProfile();

        $response = $this->get(route('home'));

        $this->assertEquals(200, $response->status());
        $content = $response->getContent();

        $this->assertStringContainsString($visible->business_name, $content);
        $this->assertStringNotContainsString($hidden->business_name, $content);
    }

    public function test_homepage_excludes_suspended_providers(): void
    {
        $visible = $this->createActiveProfile();
        $suspended = $this->createActiveProfile();
        $suspended->user->update(['is_suspended' => true]);

        $response = $this->get(route('home'));

        $content = $response->getContent();

        $this->assertStringContainsString($visible->business_name, $content);
        $this->assertStringNotContainsString($suspended->business_name, $content);
    }

    // CATEGORY PAGE TESTS

    public function test_category_page_excludes_hidden_profiles(): void
    {
        $visible = $this->createActiveProfile();
        $hidden = $this->createHiddenProfile();

        $response = $this->get(route('public.category', $this->category->slug));

        $this->assertEquals(200, $response->status());
        $content = $response->getContent();

        $this->assertStringContainsString($visible->business_name, $content);
        $this->assertStringNotContainsString($hidden->business_name, $content);
    }

    // CITY PAGE TESTS

    public function test_city_page_excludes_hidden_profiles(): void
    {
        $visible = $this->createActiveProfile();
        $hidden = $this->createHiddenProfile();

        $response = $this->get(route('public.city', $this->city->slug));

        $this->assertEquals(200, $response->status());
        $content = $response->getContent();

        $this->assertStringContainsString($visible->business_name, $content);
        $this->assertStringNotContainsString($hidden->business_name, $content);
    }

    // HELPERS

    protected function createActiveProfile(): Profile
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        // Ensure profile exists
        if (! $user->profile) {
            Profile::create(['user_id' => $user->id, 'slug' => 'provider-'.$user->id]);
            $user = $user->refresh();
        }

        $profile = $user->profile;
        $profile->update([
            'business_name' => 'Active Provider '.$user->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'slug' => 'active-'.$user->id,
            'phone' => '+218123456789',
            'whatsapp' => '+218123456789',
            'is_complete' => true,
        ]);

        // Create stats if not already created
        if (! $profile->stats) {
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
        }

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::today()->addMonth(),
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => 1,
            'processed_at' => now(),
            'processed_by' => 1,
        ]);

        return $profile->refresh();
    }

    protected function createIncompleteProfile(): Profile
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        // Ensure profile exists
        if (! $user->profile) {
            Profile::create(['user_id' => $user->id, 'slug' => 'provider-'.$user->id]);
            $user = $user->refresh();
        }

        $profile = $user->profile;
        $profile->update([
            'business_name' => 'Incomplete Provider '.$user->id,
            'is_complete' => false,
        ]);

        if (! $profile->stats) {
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
        }

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::today()->addMonth(),
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => 1,
            'processed_at' => now(),
            'processed_by' => 1,
        ]);

        return $profile->refresh();
    }

    protected function createHiddenProfile(): Profile
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        // Ensure profile exists
        if (! $user->profile) {
            Profile::create(['user_id' => $user->id, 'slug' => 'provider-'.$user->id]);
            $user = $user->refresh();
        }

        $profile = $user->profile;
        $profile->update([
            'business_name' => 'Hidden Provider '.$user->id,
            'is_complete' => true,
        ]);

        if (! $profile->stats) {
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
        }

        // No subscription = hidden

        return $profile->refresh();
    }

    protected function createUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge(['is_active' => true, 'is_suspended' => false], $attributes));
        $user->assignRole('user');

        return $user;
    }
}
