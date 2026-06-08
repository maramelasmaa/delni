<?php

namespace Tests\Feature;

use App\Data\ProfileSearchFilters;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\Subscription;
use App\Services\ProfileSearchService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests: Arabic search normalization end-to-end.
 *
 * Verifies that providers with Arabic names can be found via
 * search with variant spellings (hamza, diacritics, ta variants).
 */
class ArabicSearchIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private ProfileSearchService $searchService;

    private City $city;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->searchService = app(ProfileSearchService::class);

        // Create city and category for provider setup
        $this->city = City::create([
            'name' => 'Tripoli',
            'name_ar' => 'طرابلس',
            'slug' => 'tripoli',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Plumbing',
            'name_ar' => 'السباكة',
            'slug' => 'plumbing',
            'is_active' => true,
        ]);
    }

    /**
     * Create a provider with active subscription.
     */
    private function createActiveProvider(string $businessName): Profile
    {
        $user = $this->createProvider();
        $profile = $user->profile;

        $profile->update([
            'business_name' => $businessName,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'is_complete' => true,
        ]);

        // Create subscription using factory
        $this->createSubscriptionForProvider($user, [
            'is_active' => true,
            'ends_at' => now()->addMonth(),
        ]);

        return $profile;
    }

    private function createSubscriptionForProvider($user, $attributes = [])
    {
        $user->subscriptions()->save(
            Subscription::factory()->make($attributes)
        );
    }

    /**
     * Test: Hamza variant search - "احمد" finds "أحمد".
     */
    public function test_hamza_variant_search_ahmad(): void
    {
        $provider = $this->createActiveProvider('أحمد للسباكة');

        // Search with informal (no hamza)
        $results = $this->searchService->search(new ProfileSearchFilters(
            keyword: 'احمد'
        ));

        // Should find provider
        $this->assertGreaterThan(0, $results->total());
        $this->assertCount(1, $results->getCollection());
        $this->assertTrue($results->getCollection()->contains('id', $provider->id));
    }

    /**
     * Test: Specialist variant search - "اخصائي" finds "أخصائي".
     */
    public function test_hamza_variant_search_specialist(): void
    {
        $provider = $this->createActiveProvider('أخصائي الأسنان');

        $results = $this->searchService->search(new ProfileSearchFilters(
            keyword: 'اخصائي'
        ));

        $this->assertGreaterThan(0, $results->total());
        $this->assertTrue($results->getCollection()->contains('id', $provider->id));
    }

    /**
     * Test: Ta marbuta variant - "تقنيه" finds "تقنية".
     */
    public function test_ta_marbuta_variant_search(): void
    {
        $provider = $this->createActiveProvider('خدمات التقنية المتقدمة');

        $results = $this->searchService->search(new ProfileSearchFilters(
            keyword: 'تقنيه'
        ));

        $this->assertGreaterThan(0, $results->total());
        $this->assertTrue($results->getCollection()->contains('id', $provider->id));
    }

    /**
     * Test: Search in bio field also normalized.
     */
    public function test_search_in_bio_field(): void
    {
        $user = $this->createProvider();
        $profile = $user->profile;

        $profile->update([
            'business_name' => 'خدمات عامة',
            'bio' => 'متخصص في أعمال الدهانة والصيانة',
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'is_complete' => true,
        ]);

        $this->createSubscriptionForProvider($user, [
            'is_active' => true,
            'ends_at' => now()->addMonth(),
        ]);

        // Search in bio with variant spelling
        $results = $this->searchService->search(new ProfileSearchFilters(
            keyword: 'اعمال'
        ));

        $this->assertGreaterThan(0, $results->total());
        $this->assertTrue($results->getCollection()->contains('id', $profile->id));
    }

    /**
     * Test: Multiple variants of same word all find same provider.
     */
    public function test_multiple_variants_find_same_provider(): void
    {
        $provider = $this->createActiveProvider('أحمد محمود الخياط');

        $variants = ['احمد', 'أحمد', 'إحمد', 'آحمد'];

        foreach ($variants as $variant) {
            $results = $this->searchService->search(new ProfileSearchFilters(
                keyword: $variant
            ));

            $this->assertGreaterThan(0, $results->total(),
                "Search for '{$variant}' should find provider"
            );
            $this->assertTrue($results->getCollection()->contains('id', $provider->id),
                "Search for '{$variant}' should contain provider {$provider->id}"
            );
        }
    }

    /**
     * Test: Suspended provider hidden even if search would find them.
     */
    public function test_suspended_provider_hidden_from_search(): void
    {
        $user = $this->createProvider();
        $profile = $user->profile;

        $user->update(['is_suspended' => true]);

        $profile->update([
            'business_name' => 'أحمد للسباكة',
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'is_complete' => true,
        ]);

        $this->createSubscriptionForProvider($user, [
            'is_active' => true,
            'ends_at' => now()->addMonth(),
        ]);

        $results = $this->searchService->search(new ProfileSearchFilters(
            keyword: 'احمد'
        ));

        // Provider should not appear
        $this->assertFalse($results->getCollection()->contains('id', $profile->id));
    }

    /**
     * Test: Incomplete profile hidden from search.
     */
    public function test_incomplete_provider_hidden_from_search(): void
    {
        $user = $this->createProvider();
        $profile = $user->profile;

        $profile->update([
            'business_name' => 'أحمد للسباكة',
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'is_complete' => false, // Incomplete
        ]);

        $this->createSubscriptionForProvider($user, [
            'is_active' => true,
            'ends_at' => now()->addMonth(),
        ]);

        $results = $this->searchService->search(new ProfileSearchFilters(
            keyword: 'احمد'
        ));

        // Provider should not appear
        $this->assertFalse($results->getCollection()->contains('id', $profile->id));
    }

    /**
     * Test: No subscription provider hidden.
     */
    public function test_no_subscription_provider_hidden(): void
    {
        $user = $this->createProvider();
        $profile = $user->profile;

        $profile->update([
            'business_name' => 'أحمد للسباكة',
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'is_complete' => true,
        ]);

        $results = $this->searchService->search(new ProfileSearchFilters(
            keyword: 'احمد'
        ));

        // Provider should not appear without subscription
        $this->assertEquals(0, $results->total());
    }

    /**
     * Test: Real-world Libyan services.
     */
    public function test_libyan_services_searchable(): void
    {
        $services = [
            'أخصائي الأسنان' => 'اخصائي', // Dentist
            'دهّان ومقاول' => 'دهان',      // Painter
            'سباك محترف' => 'سباك',        // Plumber
        ];

        foreach ($services as $name => $searchTerm) {
            $provider = $this->createActiveProvider($name);

            $results = $this->searchService->search(new ProfileSearchFilters(
                keyword: $searchTerm
            ));

            $this->assertGreaterThan(0, $results->total(),
                "Service '{$name}' should be found with search '{$searchTerm}'"
            );
        }
    }

    /**
     * Test: Category filter still works with Arabic search.
     */
    public function test_category_filter_with_arabic_search(): void
    {
        $otherCategory = Category::create([
            'name' => 'Electrical',
            'name_ar' => 'كهربائي',
            'slug' => 'electrical',
            'is_active' => true,
        ]);

        // Plumbing provider
        $plumber = $this->createActiveProvider('أحمد السباك');

        // Electrical provider with similar name
        $electrician = $this->createProvider();
        $electrician = $electrician->profile;
        $electrician->update([
            'business_name' => 'أحمد الكهربائي',
            'city_id' => $this->city->id,
            'category_id' => $otherCategory->id,
            'is_complete' => true,
        ]);

        // Search for "احمد" in plumbing category only
        $results = $this->searchService->search(new ProfileSearchFilters(
            keyword: 'احمد',
            categoryId: $this->category->id,
        ));

        // Should only find plumber
        $this->assertEquals(1, $results->total());
        $this->assertTrue($results->getCollection()->contains('id', $plumber->id));
    }
}
