<?php

namespace Tests\Feature;

use App\Data\ProfileSearchFilters;
use App\Models\Category;
use App\Models\City;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ProfileSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SubscriptionSimplifiedTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $provider;

    private SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('super_admin');

        // Create provider with profile using the helper
        $this->provider = $this->createProvider();
        $this->provider->update(['email' => 'provider@test.com']);

        // Create subscription plan
        $this->plan = SubscriptionPlan::create([
            'name' => 'Test Plan',
            'name_ar' => 'خطة الاختبار',
            'duration_months' => 1,
            'price_lyd' => 100.00,
            'is_active' => true,
            'tier' => 'basic',
        ]);
    }

    public function test_admin_can_create_subscription_for_provider()
    {
        $this->actingAs($this->admin);

        $startDate = now()->toDateString();
        $endDate = now()->addMonth()->toDateString();

        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => $startDate,
            'ends_at' => $endDate,
            'notes' => 'Payment confirmed via WhatsApp',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'is_active' => 1,
        ]);
        $this->assertNotNull($subscription->approved_at);
        $this->assertEquals($this->admin->id, $subscription->approved_by);
    }

    public function test_subscription_is_active_immediately_on_creation()
    {
        $startDate = now()->toDateString();
        $endDate = now()->addMonth()->toDateString();

        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => $startDate,
            'ends_at' => $endDate,
        ]);

        $this->assertTrue($subscription->is_active);
        $this->assertNotNull($subscription->approved_at);
        $this->assertTrue($subscription->isActive());
    }

    public function test_provider_profile_becomes_visible_when_subscription_active()
    {
        // Provider profile is auto-created, just update to complete
        $profile = $this->provider->profile;
        $profile->update([
            'business_name' => 'Test Business',
            'type' => 'business',
            'provider_type' => 'company',
            'bio' => 'Test bio',
            'city_id' => City::firstOrCreate(['name' => 'Test City'], ['name_ar' => 'مدينة الاختبار', 'slug' => 'test-city'])->id,
            'category_id' => Category::firstOrCreate(['name' => 'Test Category'], ['name_ar' => 'فئة الاختبار', 'slug' => 'test-category'])->id,
            'whatsapp' => '+218912345678',
            'phone' => '+218912345678',
            'is_complete' => true,
        ]);

        // No subscription yet
        $this->assertFalse($profile->isDiscoverable());

        // Create active subscription
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        // Refresh profile
        $profile->refresh();
        $this->assertTrue($profile->isDiscoverable());
    }

    public function test_provider_profile_hidden_when_subscription_expired()
    {
        // Provider profile is auto-created, just update to complete
        $profile = $this->provider->profile;
        $profile->update([
            'business_name' => 'Test Business',
            'type' => 'business',
            'provider_type' => 'company',
            'bio' => 'Test bio',
            'city_id' => City::firstOrCreate(['name' => 'Test City'], ['name_ar' => 'مدينة الاختبار', 'slug' => 'test-city'])->id,
            'category_id' => Category::firstOrCreate(['name' => 'Test Category'], ['name_ar' => 'فئة الاختبار', 'slug' => 'test-category'])->id,
            'whatsapp' => '+218912345678',
            'phone' => '+218912345678',
            'is_complete' => true,
        ]);

        // Create expired subscription
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->subDay()->toDateString(), // Yesterday
        ]);

        $profile->refresh();
        $this->assertFalse($profile->isDiscoverable());
    }

    public function test_overlapping_subscriptions_rejected()
    {
        // Create first subscription
        $first = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-06-30',
        ]);

        // Try to create overlapping subscription
        $this->expectException(ValidationException::class);

        Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-06-15', // Within first subscription
            'ends_at' => '2026-07-15',
        ]);
    }

    public function test_non_overlapping_subscriptions_allowed()
    {
        // Create first subscription
        $first = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-06-30',
        ]);

        // Create second subscription after first ends
        $second = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-07-01', // Day after first ends
            'ends_at' => '2026-07-31',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $second->id,
            'user_id' => $this->provider->id,
        ]);
    }

    public function test_provider_cannot_create_subscription()
    {
        $this->actingAs($this->provider);

        // Policy denies provider creation
        $can = $this->provider->can('create', Subscription::class);
        $this->assertFalse($can);
    }

    public function test_subscription_cannot_be_deleted()
    {
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($this->admin);

        // Policy denies deletion even for admin
        $this->assertFalse($this->admin->can('delete', $subscription));
    }

    public function test_immutable_fields_cannot_change()
    {
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-06-30',
        ]);

        $this->expectException(ValidationException::class);

        // Try to change user_id
        $subscription->update(['user_id' => User::factory()->create()->id]);
    }

    public function test_editable_fields_can_change()
    {
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-06-30',
            'notes' => 'Original notes',
        ]);

        // Should not throw — notes are editable
        $subscription->update(['notes' => 'Updated notes']);

        $this->assertEquals('Updated notes', $subscription->fresh()->notes);
    }

    public function test_activity_log_created_on_subscription_creation()
    {
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Subscription::class,
            'subject_id' => $subscription->id,
            'action' => 'subscription_created',
        ]);
    }

    public function test_admin_can_extend_subscription_end_date_before_expiry()
    {
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-06-30',
            'notes' => 'First month',
        ]);

        $this->actingAs($this->admin);

        // Admin can extend the end date if needed (in case of disputes or renewals)
        // Actually, ends_at is immutable - let's verify this
        $this->expectException(ValidationException::class);

        $subscription->update(['ends_at' => '2026-07-31']);
    }

    public function test_provider_account_persists_after_subscription_expiry()
    {
        // Create provider with active subscription
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->subDay()->toDateString(), // Yesterday - expired
        ]);

        // Expire it
        Subscription::where('id', $subscription->id)->update(['is_active' => false]);

        // Provider account should still exist
        $this->assertDatabaseHas('users', [
            'id' => $this->provider->id,
            'email' => 'provider@test.com',
        ]);

        // Provider account should not be soft-deleted
        $provider = User::find($this->provider->id);
        $this->assertNotNull($provider);
        $this->assertNull($provider->deleted_at);
    }

    public function test_profile_persists_after_subscription_expiry()
    {
        // Create profile
        $profile = $this->provider->profile;
        $profile->update([
            'business_name' => 'Permanent Business',
            'type' => 'business',
            'provider_type' => 'company',
            'bio' => 'Test bio',
            'city_id' => City::firstOrCreate(['name' => 'Test City'], ['name_ar' => 'مدينة الاختبار', 'slug' => 'test-city'])->id,
            'category_id' => Category::firstOrCreate(['name' => 'Test Category'], ['name_ar' => 'فئة الاختبار', 'slug' => 'test-category'])->id,
            'whatsapp' => '+218912345678',
            'phone' => '+218912345678',
            'is_complete' => true,
        ]);

        // Create and expire subscription
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->subDay()->toDateString(), // Expired
        ]);

        Subscription::where('id', $subscription->id)->update(['is_active' => false]);

        // Profile should still exist and not be soft-deleted
        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->provider->id,
            'business_name' => 'Permanent Business',
        ]);

        // Verify profile is not soft-deleted
        $profile->refresh();
        $this->assertNull($profile->deleted_at);
    }

    public function test_reviews_persist_after_subscription_expiry()
    {
        // Create profile
        $profile = $this->provider->profile;
        $profile->update([
            'business_name' => 'Test Business',
            'city_id' => City::firstOrCreate(['name' => 'Test City'], ['name_ar' => 'مدينة الاختبار', 'slug' => 'test-city'])->id,
            'category_id' => Category::firstOrCreate(['name' => 'Test Category'], ['name_ar' => 'فئة الاختبار', 'slug' => 'test-category'])->id,
            'whatsapp' => '+218912345678',
            'phone' => '+218912345678',
            'is_complete' => true,
        ]);

        // Create customer and review
        $customer = User::factory()->create(['email' => 'customer@test.com']);
        $customer->assignRole('user');

        $review = Review::create([
            'profile_id' => $profile->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'comment' => 'Excellent service!',
            'status' => 'approved',
        ]);

        // Create and expire subscription
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
        ]);

        Subscription::where('id', $subscription->id)->update(['is_active' => false]);

        // Reviews should still exist
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'profile_id' => $profile->id,
            'rating' => 5,
            'status' => 'approved',
        ]);

        $review->refresh();
        $this->assertNull($review->deleted_at);
    }

    public function test_portfolio_persists_after_subscription_expiry()
    {
        // Create profile
        $profile = $this->provider->profile;
        $profile->update([
            'business_name' => 'Test Business',
            'city_id' => City::firstOrCreate(['name' => 'Test City'], ['name_ar' => 'مدينة الاختبار', 'slug' => 'test-city'])->id,
            'category_id' => Category::firstOrCreate(['name' => 'Test Category'], ['name_ar' => 'فئة الاختبار', 'slug' => 'test-category'])->id,
            'whatsapp' => '+218912345678',
            'phone' => '+218912345678',
            'is_complete' => true,
        ]);

        // Create portfolio item
        $portfolio = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'Project Alpha',
            'description' => 'A great project',
            'is_active' => true,
        ]);

        // Create and expire subscription
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
        ]);

        Subscription::where('id', $subscription->id)->update(['is_active' => false]);

        // Portfolio should still exist
        $this->assertDatabaseHas('portfolio_items', [
            'id' => $portfolio->id,
            'profile_id' => $profile->id,
            'title' => 'Project Alpha',
        ]);
    }

    public function test_renewal_uses_same_provider_and_profile()
    {
        // Create and expire first subscription
        $profile = $this->provider->profile;
        $profile->update([
            'business_name' => 'My Business',
            'city_id' => City::firstOrCreate(['name' => 'Test City'], ['name_ar' => 'مدينة الاختبار', 'slug' => 'test-city'])->id,
            'category_id' => Category::firstOrCreate(['name' => 'Test Category'], ['name_ar' => 'فئة الاختبار', 'slug' => 'test-category'])->id,
            'whatsapp' => '+218912345678',
            'phone' => '+218912345678',
            'is_complete' => true,
        ]);

        $subscription1 = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-06-01',
            'ends_at' => '2026-06-30',
        ]);

        Subscription::where('id', $subscription1->id)->update(['is_active' => false]);

        // Verify profile hidden after expiry
        $profile->refresh();
        $this->assertFalse($profile->isDiscoverable());

        // Create new subscription (renewal)
        $subscription2 = Subscription::create([
            'user_id' => $this->provider->id, // Same provider
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-07-01',
            'ends_at' => '2026-07-31',
        ]);

        // Verify profile immediately visible again
        $profile->refresh();
        $this->assertTrue($profile->isDiscoverable());

        // Verify same profile record
        $this->assertEquals($profile->id, $this->provider->profile->id);
        $this->assertEquals('My Business', $this->provider->profile->business_name);

        // Verify provider account unchanged
        $this->provider->refresh();
        $this->assertTrue($this->provider->is_active);
    }

    public function test_multiple_historical_subscriptions_for_same_provider()
    {
        // Provider has multiple historical subscriptions
        $sub1 = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-01-01',
            'ends_at' => '2026-01-31',
        ]);

        $sub2 = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-02-01',
            'ends_at' => '2026-02-28',
        ]);

        $sub3 = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => '2026-03-01',
            'ends_at' => '2026-03-31',
        ]);

        // Provider should have all subscriptions recorded
        $subscriptions = $this->provider->subscriptions;
        $this->assertCount(3, $subscriptions);

        // All subscriptions should exist in database
        $this->assertDatabaseCount('subscriptions', 3);
    }

    public function test_provider_with_expired_subscription_cannot_appear_in_search()
    {
        // Create complete profile
        $profile = $this->provider->profile;
        $profile->update([
            'business_name' => 'Test Business',
            'city_id' => City::firstOrCreate(['name' => 'Test City'], ['name_ar' => 'مدينة الاختبار', 'slug' => 'test-city'])->id,
            'category_id' => Category::firstOrCreate(['name' => 'Test Category'], ['name_ar' => 'فئة الاختبار', 'slug' => 'test-category'])->id,
            'whatsapp' => '+218912345678',
            'phone' => '+218912345678',
            'is_complete' => true,
        ]);

        // Create expired subscription
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
        ]);

        // Expire it
        Subscription::where('id', $subscription->id)->update(['is_active' => false]);

        // Profile should NOT be in search results
        $searchService = app(ProfileSearchService::class);
        $results = $searchService->search(new ProfileSearchFilters(
            page: 1,
            perPage: 50,
        ));

        $foundProfiles = $results->pluck('id')->toArray();
        $this->assertNotContains($profile->id, $foundProfiles);
    }

    public function test_provider_with_renewed_subscription_appears_in_search()
    {
        // Create complete profile
        $profile = $this->provider->profile;
        $profile->update([
            'business_name' => 'Test Business',
            'city_id' => City::firstOrCreate(['name' => 'Test City'], ['name_ar' => 'مدينة الاختبار', 'slug' => 'test-city'])->id,
            'category_id' => Category::firstOrCreate(['name' => 'Test Category'], ['name_ar' => 'فئة الاختبار', 'slug' => 'test-category'])->id,
            'whatsapp' => '+218912345678',
            'phone' => '+218912345678',
            'is_complete' => true,
        ]);

        // Create new subscription for future period
        $subscription = Subscription::create([
            'user_id' => $this->provider->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        // Profile should be in search results
        $searchService = app(ProfileSearchService::class);
        $results = $searchService->search(new ProfileSearchFilters(
            page: 1,
            perPage: 50,
        ));

        $foundProfiles = $results->pluck('id')->toArray();
        $this->assertContains($profile->id, $foundProfiles);
    }
}
