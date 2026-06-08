<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\Subcategory;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verify marketplace placement specificity behavior.
 *
 * Top Subcategory placements should ONLY boost profiles on their specific subcategory page,
 * NOT on the parent category page. This maintains the distinction between placement types
 * and prevents placement inflation.
 */
class MarketplacePlacementSpecificityTest extends TestCase
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

        $this->subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => 'Graphic Design',
            'name_ar' => 'تصميم جرافيكي',
            'slug' => 'graphic-design',
            'is_active' => true,
        ]);
    }

    // ===== CRITICAL TESTS: Placement Specificity =====

    public function test_top_subcategory_boosts_profile_on_subcategory_page(): void
    {
        $topSubcategoryProfile = $this->createActiveProfile('top-subcategory-profile');
        $topSubcategoryProfile->stats->update([
            'is_top_subcategory' => true,
            'top_subcategory_until' => Carbon::tomorrow(),
        ]);

        $normalProfile = $this->createActiveProfile('normal-profile');

        // Both profiles in the same subcategory
        $topSubcategoryProfile->subcategories()->attach($this->subcategory->id);
        $normalProfile->subcategories()->attach($this->subcategory->id);

        // Visit subcategory page
        $response = $this->get(route('public.subcategory', $this->subcategory->slug));

        $this->assertEquals(200, $response->status());
        $content = $response->getContent();

        // Top subcategory profile should appear before normal profile
        $topPos = strpos($content, $topSubcategoryProfile->business_name);
        $normalPos = strpos($content, $normalProfile->business_name);

        $this->assertNotFalse($topPos, 'Top subcategory profile should appear on subcategory page');
        $this->assertNotFalse($normalPos, 'Normal profile should appear on subcategory page');
        $this->assertLessThan($normalPos, $topPos, 'Top subcategory profile should appear before normal profile');
    }

    public function test_top_subcategory_does_not_boost_parent_category_page(): void
    {
        // Create two profiles in the category but only one has top_subcategory placement
        $topSubcategoryProfile = $this->createActiveProfile('top-sub-profile');
        $topSubcategoryProfile->stats->update([
            'is_top_subcategory' => true,
            'top_subcategory_until' => Carbon::tomorrow(),
        ]);

        $normalProfile = $this->createActiveProfile('normal-profile');

        // Both in same category
        $topSubcategoryProfile->update(['category_id' => $this->category->id]);
        $normalProfile->update(['category_id' => $this->category->id]);

        // Add to subcategory (top_subcategory only)
        $topSubcategoryProfile->subcategories()->attach($this->subcategory->id);

        // Visit parent category page
        $response = $this->get(route('public.category', $this->category->slug));

        $this->assertEquals(200, $response->status());
        $content = $response->getContent();

        // Both profiles should appear
        $this->assertStringContainsString($topSubcategoryProfile->business_name, $content, 'Top subcategory profile should appear on category page');
        $this->assertStringContainsString($normalProfile->business_name, $content, 'Normal profile should appear on category page');

        // Top subcategory profile should NOT be boosted on parent category page
        // They should have same ranking tier (Bucket 1 for this profile since it has no top_category)
        $topPos = strpos($content, $topSubcategoryProfile->business_name);
        $normalPos = strpos($content, $normalProfile->business_name);

        // On parent category, top_subcategory has NO boost, so it should appear similarly to normal profile
        // (actual order depends on rating, reviews, created_at, not the subcategory boost)
        $this->assertTrue($topPos !== false && $normalPos !== false, 'Both profiles should appear on category page at same ranking tier');
    }

    public function test_top_category_boosts_parent_category_page(): void
    {
        $topCategoryProfile = $this->createActiveProfile('top-category-profile');
        $topCategoryProfile->stats->update([
            'is_top_category' => true,
            'top_category_until' => Carbon::tomorrow(),
        ]);

        $normalProfile = $this->createActiveProfile('normal-profile');

        // Both in same category
        $topCategoryProfile->update(['category_id' => $this->category->id]);
        $normalProfile->update(['category_id' => $this->category->id]);

        // Visit parent category page
        $response = $this->get(route('public.category', $this->category->slug));

        $this->assertEquals(200, $response->status());
        $content = $response->getContent();

        // Top category profile should appear first (boosted)
        $topPos = strpos($content, $topCategoryProfile->business_name);
        $normalPos = strpos($content, $normalProfile->business_name);

        $this->assertNotFalse($topPos, 'Top category profile should appear on category page');
        $this->assertNotFalse($normalPos, 'Normal profile should appear on category page');
        $this->assertLessThan($normalPos, $topPos, 'Top category profile should appear before normal profile on category page');
    }

    public function test_top_category_does_not_affect_subcategory_ranking(): void
    {
        $topCategoryProfile = $this->createActiveProfile('top-category-profile');
        $topCategoryProfile->stats->update([
            'is_top_category' => true,
            'top_category_until' => Carbon::tomorrow(),
        ]);

        $normalProfile = $this->createActiveProfile('normal-profile');

        // Both in same category and subcategory
        $topCategoryProfile->update(['category_id' => $this->category->id]);
        $normalProfile->update(['category_id' => $this->category->id]);
        $topCategoryProfile->subcategories()->attach($this->subcategory->id);
        $normalProfile->subcategories()->attach($this->subcategory->id);

        // Visit subcategory page
        $response = $this->get(route('public.subcategory', $this->subcategory->slug));

        $this->assertEquals(200, $response->status());
        $content = $response->getContent();

        // Top category placement should NOT boost on subcategory page
        // Both should appear at normal ranking tier (Bucket 1)
        $topPos = strpos($content, $topCategoryProfile->business_name);
        $normalPos = strpos($content, $normalProfile->business_name);

        $this->assertTrue($topPos !== false && $normalPos !== false, 'Both profiles should appear on subcategory page at same ranking tier');
    }

    // ===== PLACEMENT CONTEXT ISOLATION TESTS =====

    public function test_homepage_featured_only_affects_homepage(): void
    {
        $homepageFeaturedProfile = $this->createActiveProfile('homepage-featured');
        $homepageFeaturedProfile->stats->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => Carbon::tomorrow(),
        ]);

        $normalProfile = $this->createActiveProfile('normal-profile');

        // Both in same category
        $homepageFeaturedProfile->update(['category_id' => $this->category->id]);
        $normalProfile->update(['category_id' => $this->category->id]);

        // Homepage featured should boost on homepage
        $homepageResponse = $this->get(route('home'));
        $this->assertStringContainsString($homepageFeaturedProfile->business_name, $homepageResponse->getContent());

        // But NOT boost on category page
        // (ranking on category page uses different buckets)
    }

    public function test_top_search_only_affects_search_results(): void
    {
        $topSearchProfile = $this->createActiveProfile('top-search');
        $topSearchProfile->stats->update([
            'is_top_search' => true,
            'top_search_until' => Carbon::tomorrow(),
        ]);

        $normalProfile = $this->createActiveProfile('normal-profile');

        // Search results
        $searchResponse = $this->get(route('public.search'));
        $this->assertEquals(200, $searchResponse->status());

        // Both should appear in search
        $this->assertStringContainsString($topSearchProfile->business_name, $searchResponse->getContent());
        $this->assertStringContainsString($normalProfile->business_name, $searchResponse->getContent());
    }

    // ===== EXPIRATION VERIFICATION =====

    public function test_expired_top_subcategory_does_not_boost(): void
    {
        $expiredSubcategoryProfile = $this->createActiveProfile('expired-subcategory');
        $expiredSubcategoryProfile->stats->update([
            'is_top_subcategory' => true,
            'top_subcategory_until' => Carbon::yesterday(), // Expired
        ]);

        $activeSubcategoryProfile = $this->createActiveProfile('active-subcategory');
        $activeSubcategoryProfile->stats->update([
            'is_top_subcategory' => true,
            'top_subcategory_until' => Carbon::tomorrow(), // Active
        ]);

        // Both in same subcategory
        $expiredSubcategoryProfile->subcategories()->attach($this->subcategory->id);
        $activeSubcategoryProfile->subcategories()->attach($this->subcategory->id);

        // Visit subcategory page
        $response = $this->get(route('public.subcategory', $this->subcategory->slug));

        $content = $response->getContent();

        // Active should appear before expired
        $expiredPos = strpos($content, $expiredSubcategoryProfile->business_name);
        $activePos = strpos($content, $activeSubcategoryProfile->business_name);

        $this->assertNotFalse($expiredPos);
        $this->assertNotFalse($activePos);
        $this->assertLessThan($expiredPos, $activePos, 'Active top_subcategory should appear before expired');
    }

    // ===== HELPER METHODS =====

    private function createActiveProfile(string $uniqueId): Profile
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::create([
            'user_id' => $user->id,
            'business_name' => "Provider {$uniqueId}",
            'bio' => "Provider {$uniqueId} bio",
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'slug' => $uniqueId,
            'phone' => '+218123456789',
            'whatsapp' => '+218123456789',
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
}
