<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\ContactInfo;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MarketplaceApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $providerUser;

    private Profile $profile;

    private Category $category;

    private Subcategory $subcategory;

    private City $city;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('provider', 'web');
        Role::findOrCreate('user', 'web');

        $this->category = Category::factory()->create(['is_active' => true]);
        $this->subcategory = Subcategory::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);
        $this->city = City::factory()->create(['is_active' => true]);

        $this->providerUser = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $this->providerUser->assignRole('provider');

        $this->profile = Profile::factory()->create([
            'user_id' => $this->providerUser->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'is_complete' => true,
            'provider_access_ends_at' => now()->addDays(30),
            'logo' => 'profiles/logos/logo.webp',
            'cover_image' => 'profiles/covers/cover.webp',
        ]);

        $this->profile->subcategories()->attach($this->subcategory);

        ProfileStats::factory()->create([
            'profile_id' => $this->profile->id,
            'rating_avg' => 5.0,
            'reviews_count' => 5,
        ]);
    }

    public function test_health_endpoint_works(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'خادم دلني يعمل بنجاح.',
            ]);
    }

    public function test_home_endpoint_works(): void
    {
        $this->getJson('/api/v1/home')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'stats' => [
                        'visible_providers_count',
                        'categories_count',
                        'cities_count',
                        'reviews_count',
                    ],
                    'categories',
                    'featured_providers',
                    'suggested_providers',
                ],
            ]);
    }

    public function test_home_endpoint_city_filter_is_not_sticky_across_requests(): void
    {
        $secondCity = City::factory()->create(['is_active' => true]);
        $secondProvider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $secondProvider->assignRole('provider');

        $secondProfile = Profile::factory()->create([
            'user_id' => $secondProvider->id,
            'city_id' => $secondCity->id,
            'category_id' => $this->category->id,
            'is_complete' => true,
            'provider_access_ends_at' => now()->addDays(30),
        ]);

        $secondProfile->subcategories()->attach($this->subcategory);

        ProfileStats::factory()->create([
            'profile_id' => $secondProfile->id,
            'rating_avg' => 4.8,
            'reviews_count' => 6,
        ]);

        $this->getJson('/api/v1/home?city='.$this->city->slug)
            ->assertOk()
            ->assertJsonPath('data.stats.visible_providers_count', 1);

        $this->getJson('/api/v1/home')
            ->assertOk()
            ->assertJsonPath('data.stats.visible_providers_count', 2);
    }

    public function test_categories_endpoint_works(): void
    {
        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'slug',
                        'name',
                        'icon_url',
                        'providers_count',
                        'subcategories_count',
                    ],
                ],
            ]);
    }

    public function test_search_hides_expired_providers(): void
    {
        $this->getJson('/api/v1/search?q='.urlencode($this->profile->business_name))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->profile->update(['provider_access_ends_at' => now()->subDay()]);

        $this->getJson('/api/v1/search?q='.urlencode($this->profile->business_name))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_category_hides_suspended_providers(): void
    {
        $this->getJson("/api/v1/categories/{$this->category->slug}")
            ->assertOk()
            ->assertJsonCount(1, 'data.providers');

        $this->providerUser->update(['is_suspended' => true]);

        $this->getJson("/api/v1/categories/{$this->category->slug}")
            ->assertOk()
            ->assertJsonCount(0, 'data.providers');
    }

    public function test_provider_profile_returns_404_for_expired_provider(): void
    {
        $this->getJson("/api/v1/providers/{$this->profile->slug}")->assertOk();

        $this->profile->update(['provider_access_ends_at' => now()->subDay()]);

        $this->getJson("/api/v1/providers/{$this->profile->slug}")
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'العنصر غير موجود.',
            ]);
    }

    public function test_top_rated_excludes_expired_high_rated_provider(): void
    {
        $this->getJson('/api/v1/top-rated')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->profile->update(['provider_access_ends_at' => now()->subDay()]);

        $this->getJson('/api/v1/top-rated')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_favorites_require_auth(): void
    {
        $this->getJson('/api/v1/favorites')->assertUnauthorized();
        $this->postJson("/api/v1/favorites/{$this->profile->slug}")->assertUnauthorized();
        $this->deleteJson("/api/v1/favorites/{$this->profile->slug}")->assertUnauthorized();
    }

    public function test_favorites_crud_works_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/favorites/{$this->profile->slug}")
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'تم إضافة المزود إلى المفضلة بنجاح.']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/favorites')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $this->profile->slug);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/favorites/{$this->profile->slug}")
            ->assertOk()
            ->assertJson(['success' => true, 'message' => 'تم إزالة المزود من المفضلة بنجاح.']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/favorites')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_favorites_endpoints_resolve_provider_slug_internally(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/favorites/{$this->profile->id}")
            ->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'العنصر غير موجود.',
            ]);
    }

    public function test_favorites_exclude_invisible_providers(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/favorites/{$this->profile->slug}")
            ->assertOk();

        $this->providerUser->update(['is_active' => false]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/favorites')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_api_messages_remain_arabic_even_with_accept_language_header(): void
    {
        $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'خادم دلني يعمل بنجاح.',
            ]);
    }

    public function test_review_requires_auth(): void
    {
        $this->postJson("/api/v1/providers/{$this->profile->slug}/reviews", [
            'rating' => 5,
            'comment' => 'عظيم',
        ])->assertUnauthorized();
    }

    public function test_duplicate_review_rule_works(): void
    {
        $user = User::factory()->create(['created_at' => now()->subDays(2)]);
        $user->assignRole('user');

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/providers/{$this->profile->slug}/reviews", [
                'rating' => 5,
                'comment' => 'رائع',
            ])->assertOk();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/providers/{$this->profile->slug}/reviews", [
                'rating' => 4,
                'comment' => 'محاولة تكرار',
            ])->assertUnprocessable()
            ->assertJsonValidationErrors(['profile']);
    }

    public function test_rejected_review_allows_retry(): void
    {
        $user = User::factory()->create(['created_at' => now()->subDays(2)]);
        $user->assignRole('user');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/providers/{$this->profile->slug}/reviews", [
                'rating' => 5,
                'comment' => 'سوف يتم رفضه',
            ])->assertOk();

        $reviewId = $response->json('data.id');

        $review = Review::findOrFail($reviewId);
        $review->update(['status' => ReviewStatus::REJECTED]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/providers/{$this->profile->slug}/reviews", [
                'rating' => 4,
                'comment' => 'مراجعة جديدة مقبولة',
            ])->assertOk();
    }

    public function test_image_urls_are_absolute(): void
    {
        $response = $this->getJson("/api/v1/providers/{$this->profile->slug}")
            ->assertOk();

        $logoUrl = $response->json('data.logo_url');
        $coverUrl = $response->json('data.cover_url');

        $this->assertStringStartsWith('http', $logoUrl);
        $this->assertStringContainsString('/storage/profiles/logos/logo.webp', $logoUrl);
        $this->assertStringStartsWith('http', $coverUrl);
        $this->assertStringContainsString('/storage/profiles/covers/cover.webp', $coverUrl);
    }

    public function test_home_endpoint_serializes_provider_name_without_lazy_loading_user_relation(): void
    {
        $this->profile->update(['business_name' => null]);
        ProfileStats::where('profile_id', $this->profile->id)->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => now()->addDay()->toDateString(),
        ]);

        $response = $this->getJson('/api/v1/home')
            ->assertOk();

        $this->assertSame($this->providerUser->name, $response->json('data.featured_providers.0.name'));
    }

    public function test_user_without_provider_role_is_excluded_from_all_listings(): void
    {
        // The role-based whereExists subquery in applyVisibleQuery is the most critical
        // security clause — regression would expose non-provider users as providers.
        $regularUser = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $regularUser->assignRole('user');

        $nonProviderProfile = Profile::factory()->create([
            'user_id' => $regularUser->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'is_complete' => true,
            'provider_access_ends_at' => now()->addDays(30),
        ]);

        // ProfileFactory::configure() assigns 'provider' role via afterCreating hook.
        // Revoke it so the user has only the 'user' role — the scenario under test.
        $regularUser->syncRoles(['user']);

        ProfileStats::factory()->create(['profile_id' => $nonProviderProfile->id]);

        $homeIds = collect($this->getJson('/api/v1/home')->assertOk()->json('data.featured_providers') ?? [])->pluck('id');
        $this->assertNotContains($nonProviderProfile->id, $homeIds);

        $searchIds = $this->getJson('/api/v1/search')->assertOk()->json('data.*.id');
        $this->assertNotContains($nonProviderProfile->id, $searchIds ?? []);

        $this->getJson("/api/v1/providers/{$nonProviderProfile->slug}")->assertNotFound();
    }

    public function test_incomplete_profile_is_excluded_from_all_listings(): void
    {
        // profiles.user_id is unique — cannot reuse $this->providerUser who already has a profile.
        $anotherProvider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $anotherProvider->assignRole('provider');

        $incompleteProfile = Profile::factory()->create([
            'user_id' => $anotherProvider->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'is_complete' => false,
            'provider_access_ends_at' => now()->addDays(30),
        ]);

        // ProfileFactory::configure() assigns 'provider' role — keep it; the user IS a provider,
        // but the profile is incomplete, so it must still be hidden.
        ProfileStats::factory()->create(['profile_id' => $incompleteProfile->id]);

        $searchIds = $this->getJson('/api/v1/search')->assertOk()->json('data.*.id');
        $this->assertNotContains($incompleteProfile->id, $searchIds ?? []);

        $this->getJson("/api/v1/providers/{$incompleteProfile->slug}")->assertNotFound();
    }

    public function test_image_url_host_matches_app_url(): void
    {
        $response = $this->getJson("/api/v1/providers/{$this->profile->slug}")->assertOk();

        $logoUrl = $response->json('data.logo_url');
        $parsedHost = parse_url($logoUrl, PHP_URL_HOST);
        $expectedHost = parse_url(config('app.url'), PHP_URL_HOST);

        $this->assertNotNull($parsedHost, 'Image URL has no host — it is relative, not absolute');
        $this->assertSame($expectedHost, $parsedHost, "Image URL host '{$parsedHost}' does not match APP_URL host '{$expectedHost}'");
    }

    public function test_contact_endpoint_returns_contact_info(): void
    {
        ContactInfo::create([
            'whatsapp' => '0911234567',
            'phone' => '0917654321',
            'email' => 'support@example.com',
            'facebook' => 'https://facebook.com/example',
            'address' => 'Tripoli, Libya',
        ]);

        $this->getJson('/api/v1/contact')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'whatsapp' => '0911234567',
                    'phone' => '0917654321',
                    'email' => 'support@example.com',
                    'facebook' => 'https://facebook.com/example',
                    'address' => 'Tripoli, Libya',
                ],
            ]);
    }
}
