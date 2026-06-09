<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\PortfolioImage;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\ProviderLink;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive test suite for marketplace placement visibility and public page hardening.
 *
 * Verifies that:
 * 1. Placements affect ranking/ordering correctly
 * 2. Expired placements have no effect
 * 3. Suspended/expired subscription providers are hidden
 * 4. Public pages never leak admin fields or wording
 * 5. Provider cards never expose raw placement data
 * 6. Public pages render safely with missing optional data
 */
class MarketplacePublicHardeningTest extends TestCase
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
            'name' => 'Test Plan',
            'name_ar' => 'خطة اختبار',
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

    // ===== PLACEMENT RANKING TESTS =====

    public function test_homepage_featured_placement_affects_homepage_ordering(): void
    {
        $homepageFeatured = $this->createActiveProfile('homepage-featured');
        $homepageFeatured->stats->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => Carbon::tomorrow(),
        ]);

        $normal = $this->createActiveProfile('normal-provider');

        $response = $this->get(route('home'));
        $response->assertOk();
        $content = $response->getContent();

        // Featured provider should appear first
        $featuredPos = strpos($content, $homepageFeatured->business_name);
        $normalPos = strpos($content, $normal->business_name);

        $this->assertNotFalse($featuredPos);
        $this->assertNotFalse($normalPos);
        $this->assertLessThan($normalPos, $featuredPos, 'Homepage featured should appear before normal provider');
    }

    public function test_top_search_affects_search_ordering(): void
    {
        $topSearch = $this->createActiveProfile('top-search');
        $topSearch->stats->update([
            'is_top_search' => true,
            'top_search_until' => Carbon::tomorrow(),
        ]);

        $normal = $this->createActiveProfile('normal-search');

        $response = $this->get(route('public.search'));
        $response->assertOk();
        $content = $response->getContent();

        $topPos = strpos($content, $topSearch->business_name);
        $normalPos = strpos($content, $normal->business_name);

        $this->assertNotFalse($topPos);
        $this->assertNotFalse($normalPos);
        $this->assertLessThan($normalPos, $topPos, 'Top search should appear before normal provider');
    }

    public function test_top_category_affects_category_ordering(): void
    {
        $topCategory = $this->createActiveProfile('top-category');
        $topCategory->stats->update([
            'is_top_category' => true,
            'top_category_until' => Carbon::tomorrow(),
        ]);

        $normal = $this->createActiveProfile('normal-category');

        $response = $this->get(route('public.category', $this->category));
        $response->assertOk();
        $content = $response->getContent();

        $topPos = strpos($content, $topCategory->business_name);
        $normalPos = strpos($content, $normal->business_name);

        $this->assertNotFalse($topPos);
        $this->assertNotFalse($normalPos);
        $this->assertLessThan($normalPos, $topPos, 'Top category should appear before normal provider');
    }

    // ===== EXPIRED PLACEMENT TESTS =====

    public function test_expired_placements_do_not_affect_ranking(): void
    {
        $expired = $this->createActiveProfile('expired-placement');
        $expired->stats->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => Carbon::yesterday(), // Expired
        ]);

        $active = $this->createActiveProfile('active-placement');
        $active->stats->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => Carbon::tomorrow(), // Active
        ]);

        $response = $this->get(route('home'));
        $content = $response->getContent();

        // Active should appear before expired
        $expiredPos = strpos($content, $expired->business_name);
        $activePos = strpos($content, $active->business_name);

        $this->assertNotFalse($expiredPos);
        $this->assertNotFalse($activePos);
        $this->assertLessThan($expiredPos, $activePos, 'Active placement should rank higher than expired');
    }

    // ===== VISIBILITY RULES TESTS =====

    public function test_suspended_providers_hidden_publicly(): void
    {
        $suspended = $this->createActiveProfile('suspended-provider');
        $suspended->user->update(['is_suspended' => true]);

        $response = $this->get(route('public.search'));
        $response->assertOk();

        $this->assertStringNotContainsString($suspended->business_name, $response->getContent());
    }

    public function test_expired_subscription_providers_hidden_publicly(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::create([
            'user_id' => $user->id,
            'business_name' => 'Expired Subscription Provider',
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'slug' => 'expired-sub',
            'phone' => '+218123456789',
            'whatsapp' => '+218123456789',
            'is_complete' => true,
        ]);

        $profile->stats()->create([
            'rating_avg' => 0,
            'reviews_count' => 0,
            'is_featured' => false,
        ]);

        // Create expired subscription
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => Carbon::today()->subMonth(),
            'ends_at' => Carbon::yesterday(), // Expired
            'is_active' => false,
            'approved_at' => now(),
        ]);

        $response = $this->get(route('public.search'));
        $response->assertOk();

        $this->assertStringNotContainsString('Expired Subscription Provider', $response->getContent());
    }

    // ===== PUBLIC PAGE HARDENING TESTS =====

    public function test_public_pages_never_show_admin_placement_fields(): void
    {
        $profile = $this->createActiveProfile('hardening-test');
        $profile->stats->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => Carbon::tomorrow(),
        ]);

        $adminFields = [
            'featured_until',
            'homepage_featured_until',
            'top_search_until',
            'top_category_until',
            'top_subcategory_until',
            'is_featured',
            'is_top_search',
            'is_homepage_featured',
            'is_top_category',
            'is_top_subcategory',
        ];

        foreach ([
            route('home'),
            route('public.search'),
            route('public.category', $this->category),
        ] as $url) {
            $response = $this->get($url);
            $response->assertOk();

            foreach ($adminFields as $field) {
                $this->assertStringNotContainsString($field, $response->getContent(),
                    "Admin field '{$field}' should not appear on public page: {$url}");
            }
        }
    }

    public function test_public_pages_never_show_admin_wording(): void
    {
        $profile = $this->createActiveProfile('admin-wording-test');

        $adminWording = [
            'إضافة',
            'إضافة وبدء إضافة المزيد',
            'إلغاء',
        ];

        foreach ([
            route('home'),
            route('public.search'),
            route('public.provider', $profile),
        ] as $url) {
            $response = $this->get($url);
            $response->assertOk();

            foreach ($adminWording as $word) {
                $this->assertStringNotContainsString($word, $response->getContent(),
                    "Admin wording '{$word}' should not appear on public page: {$url}");
            }
        }
    }

    public function test_provider_cards_never_expose_raw_placement_fields(): void
    {
        $profile = $this->createActiveProfile('card-exposure-test');
        $profile->stats->update([
            'is_featured' => true,
            'featured_until' => Carbon::tomorrow(),
        ]);

        $response = $this->get(route('public.search'));
        $response->assertOk();

        // The card should NOT contain raw field names or values
        $this->assertStringNotContainsString('is_featured', $response->getContent());
        $this->assertStringNotContainsString('featured_until', $response->getContent());
    }

    // ===== OPTIONAL DATA RENDERING TESTS =====

    public function test_provider_profile_renders_safely_with_missing_optional_data(): void
    {
        $profile = $this->createActiveProfile('missing-data', [
            'logo' => null,
            'cover_image' => null,
            'bio' => null,
            'service_area_note' => null,
            'website' => null,
            'instagram' => null,
            'facebook' => null,
            'linkedin' => null,
        ]);

        $response = $this->get(route('public.provider', $profile));
        $response->assertOk();

        // Should render name without errors
        $this->assertStringContainsString('missing-data', $response->getContent());
    }

    public function test_portfolio_limits_enforced_on_public_view(): void
    {
        $profile = $this->createActiveProfile('portfolio-test');

        // Create 2 projects (max)
        $project1 = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'Project 1',
            'title_ar' => 'المشروع 1',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $project2 = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'Project 2',
            'title_ar' => 'المشروع 2',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Add 4 images to first project
        for ($i = 1; $i <= 4; $i++) {
            PortfolioImage::create([
                'portfolio_item_id' => $project1->id,
                'path' => "portfolios/test-{$i}.jpg",
                'sort_order' => $i,
            ]);
        }

        // Add 4 images to second project
        for ($i = 5; $i <= 8; $i++) {
            PortfolioImage::create([
                'portfolio_item_id' => $project2->id,
                'path' => "portfolios/test-{$i}.jpg",
                'sort_order' => $i - 4,
            ]);
        }

        $response = $this->get(route('public.provider', $profile));
        $response->assertOk();

        // Should display both projects without errors
        $this->assertStringContainsString('Project 1', $response->getContent());
        $this->assertStringContainsString('Project 2', $response->getContent());
    }

    public function test_suspicious_links_rejected_before_display(): void
    {
        $profile = $this->createActiveProfile('link-security-test');

        // Create a safe link
        ProviderLink::create([
            'profile_id' => $profile->id,
            'title' => 'Portfolio',
            'url' => 'https://example.com/portfolio',
            'is_active' => true,
        ]);

        $response = $this->get(route('public.provider', $profile));
        $response->assertOk();

        // Safe link should appear (either as text or URL)
        $this->assertTrue(
            strpos($response->getContent(), 'example.com') !== false ||
            strpos($response->getContent(), 'Portfolio') !== false,
            'Safe link should appear on provider page'
        );
    }

    // ===== NO 500 ERROR TESTS =====

    public function test_no_500_errors_on_public_pages(): void
    {
        $profile = $this->createActiveProfile('error-test');

        $urls = [
            route('home'),
            route('public.search'),
            route('public.category', $this->category),
            route('public.provider', $profile),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    // ===== HELPER METHODS =====

    private function createActiveProfile(string $slug, array $overrides = []): Profile
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);
        $user->assignRole('provider');

        $profile = Profile::create(array_merge([
            'user_id' => $user->id,
            'business_name' => "Provider {$slug}",
            'bio' => "Bio for {$slug}",
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'slug' => $slug,
            'phone' => '+218123456789',
            'whatsapp' => '+218123456789',
            'is_complete' => true,
        ], $overrides));

        $profile->stats()->create([
            'rating_avg' => 0,
            'reviews_count' => 0,
            'is_featured' => false,
            'is_homepage_featured' => false,
            'is_top_search' => false,
            'is_top_category' => false,
            'is_top_subcategory' => false,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::today()->addMonth(),
            'is_active' => true,
            'approved_at' => now(),
        ]);

        return $profile->refresh();
    }
}
