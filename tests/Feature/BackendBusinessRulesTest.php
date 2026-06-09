<?php

namespace Tests\Feature;

use App\Data\ProfileSearchFilters;
use App\Enums\ReviewStatus;
use App\Filament\Resources\ProviderResource;
use App\Filament\Resources\UserResource;
use App\Models\Category;
use App\Models\City;
use App\Models\PortfolioImage;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\ProviderCredential;
use App\Models\ProviderLink;
use App\Models\ProviderType;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ProfileCompletenessService;
use App\Services\ProfileImageService;
use App\Services\ProfileSearchService;
use App\Services\ProfileStatsService;
use App\Services\ProfileVisibilityService;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BackendBusinessRulesTest extends TestCase
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
            'name' => 'Test Plan',
            'name_ar' => 'خطة اختبار',
            'duration_months' => 1,
            'price_lyd' => 100,
            'is_active' => true,
        ]);
        $this->city = City::create(['name' => 'Tripoli', 'name_ar' => 'طرابلس', 'slug' => 'tripoli', 'is_active' => true]);
        $this->category = Category::create(['name' => 'Design', 'name_ar' => 'تصميم', 'slug' => 'design', 'is_active' => true]);
        $this->subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name' => 'Logo',
            'name_ar' => 'شعار',
            'slug' => 'logo',
            'is_active' => true,
        ]);
    }

    public function test_authentication_blocks_inactive_suspended_locked_and_soft_deleted_users(): void
    {
        $active = $this->user('user', ['email' => 'active@example.test']);
        $inactive = $this->user('user', ['email' => 'inactive@example.test', 'is_active' => false]);
        $suspended = $this->user('user', ['email' => 'suspended@example.test', 'is_suspended' => true]);
        $locked = $this->user('user', ['email' => 'locked@example.test', 'locked_until' => now()->addMinutes(15)]);
        $deleted = $this->user('user', ['email' => 'deleted@example.test']);
        $deleted->delete();

        $this->post('/login', ['email' => $active->email, 'password' => 'Password123!'])
            ->assertRedirect('/dashboard');

        auth()->logout();

        foreach ([$inactive, $suspended, $locked, $deleted] as $user) {
            $this->post('/login', ['email' => $user->email, 'password' => 'Password123!'])
                ->assertSessionHasErrors('email');
        }
    }

    public function test_profile_completeness_requires_phone_and_whatsapp_but_not_photo_and_visibility_requires_subscription(): void
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider, ['logo' => null, 'cover_image' => null, 'is_complete' => false, 'phone' => '+218911111111', 'whatsapp' => '+218911111111']);

        app(ProfileCompletenessService::class)->evaluate($profile);
        $profile->refresh();

        $this->assertTrue($profile->is_complete);
        $this->assertFalse(app(ProfileVisibilityService::class)->isDiscoverable($profile));

        $this->activeSubscription($provider);

        $this->assertTrue(app(ProfileVisibilityService::class)->isDiscoverable($profile->refresh()));

        $provider->update(['is_suspended' => true]);
        $this->assertFalse(app(ProfileVisibilityService::class)->isDiscoverable($profile->refresh()));
    }

    public function test_optional_provider_links_and_credentials_do_not_change_completeness_visibility_or_search_ranking(): void
    {
        $normal = $this->discoverableProvider('normal-flexible', []);
        $featured = $this->discoverableProvider('featured-flexible', ['is_featured' => true, 'featured_until' => now()->addDays(3)]);

        ProviderLink::create([
            'profile_id' => $normal['profile']->id,
            'label' => 'Website',
            'url' => 'https://example.test',
            'sort_order' => 1,
        ]);
        ProviderCredential::create([
            'profile_id' => $normal['profile']->id,
            'title' => 'Certified Provider',
            'issuer' => 'Delni',
            'verification_url' => 'https://verify.example.test',
            'issue_date' => now()->toDateString(),
            'notes' => 'Optional credential',
        ]);

        app(ProfileCompletenessService::class)->evaluate($normal['profile']->refresh());

        $this->assertTrue($normal['profile']->refresh()->is_complete);
        $this->assertTrue(app(ProfileVisibilityService::class)->isDiscoverable($normal['profile']));

        $results = app(ProfileSearchService::class)
            ->search(new ProfileSearchFilters(perPage: 10))
            ->getCollection();

        $this->assertSame($featured['profile']->id, $results->first()->id);
        $this->assertTrue($results->contains('id', $normal['profile']->id));

        $this->get('/providers/'.$normal['profile']->slug)
            ->assertOk()
            ->assertSee('Website')
            ->assertSee('Certified Provider')
            ->assertSee('https://wa.me/218911234567')
            ->assertDontSee('Query count')
            ->assertDontSee('Duplicate queries');
    }

    public function test_search_api_and_service_apply_discoverability_filters_and_ranking_buckets(): void
    {
        $normal = $this->discoverableProvider('normal', []);
        $topRated = $this->discoverableProvider('top-rated', ['is_top_rated' => true, 'rating_avg' => 4.8, 'reviews_count' => 7]);
        for ($i = 1; $i <= 5; $i++) {
            Review::create([
                'profile_id' => $topRated['profile']->id,
                'user_id' => $this->user('user', ['email' => "top-rated-reviewer-{$i}@example.test"])->id,
                'rating' => 5,
                'status' => ReviewStatus::APPROVED,
                'comment' => "Top-rated review {$i}",
            ]);
        }
        $featured = $this->discoverableProvider('featured', ['is_featured' => true, 'featured_until' => now()->addDays(3)]);
        $topSubcategory = $this->discoverableProvider('top-subcategory', ['is_top_subcategory' => true, 'top_subcategory_until' => now()->addDays(3)]);
        $topCategory = $this->discoverableProvider('top-category', ['is_top_category' => true, 'top_category_until' => now()->addDays(3)]);
        $topSearch = $this->discoverableProvider('top-search', ['is_top_search' => true, 'top_search_until' => now()->addDays(3)]);
        $homepage = $this->discoverableProvider('homepage', ['is_homepage_featured' => true, 'homepage_featured_until' => now()->addDays(3)]);

        $hidden = $this->discoverableProvider('hidden', []);
        $hidden['user']->update(['is_suspended' => true]);

        DB::enableQueryLog();
        $results = app(ProfileSearchService::class)->search(new ProfileSearchFilters(perPage: 20));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(8, $queryCount);
        $this->assertSame([
            $homepage['profile']->id,
            $topSearch['profile']->id,
            $topCategory['profile']->id,
            $topSubcategory['profile']->id,
            $featured['profile']->id,
            $topRated['profile']->id,
            $normal['profile']->id,
        ], $results->getCollection()->pluck('id')->all());
        $this->assertFalse($results->getCollection()->contains('id', $hidden['profile']->id));

        $this->getJson('/api/profiles/search?per_page=20')
            ->assertOk()
            ->assertJsonPath('pagination.total', 7);
    }

    public function test_search_filters_keyword_city_category_and_subcategory(): void
    {
        $match = $this->discoverableProvider('alpha-special', []);
        $otherCity = City::create(['name' => 'Benghazi', 'name_ar' => 'بنغازي', 'slug' => 'benghazi', 'is_active' => true]);
        $miss = $this->discoverableProvider('beta-other', [], ['city_id' => $otherCity->id, 'bio' => 'Different']);

        $this->assertSearchContains(['keyword' => 'alpha', 'per_page' => 5], $match['profile'], $miss['profile']);
        $this->assertSearchContains(['city_id' => $this->city->id, 'per_page' => 5], $match['profile'], $miss['profile']);
        $this->assertSearchContains(['category_id' => $this->category->id, 'per_page' => 5], $match['profile'], null);
        $this->assertSearchContains(['subcategory_id' => $this->subcategory->id, 'per_page' => 5], $match['profile'], null);
    }

    public function test_subscription_rules_reject_non_provider_overlap_bad_dates_and_immutable_financial_records(): void
    {
        $publicUser = $this->user('user');
        $provider = $this->user('provider');

        $this->expectException(ValidationException::class);
        $this->subscription($publicUser, now(), now()->addMonth());
    }

    public function test_subscription_date_and_immutability_rules(): void
    {
        $provider = $this->user('provider');

        try {
            $this->subscription($provider, now(), now());
            $this->fail('Expected same-day subscription dates to fail.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $subscription = $this->subscription($provider, now(), now()->addMonth());

        try {
            $this->subscription($provider, now()->addDays(10), now()->addDays(40));
            $this->fail('Expected overlapping subscription dates to fail.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $this->expectException(ValidationException::class);
        $subscription->update(['ends_at' => now()->addMonths(2)]);
    }

    public function test_expiry_commands_clear_subscriptions_placements_and_top_rated_recalculation(): void
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider);
        $this->subscription($provider, now()->subMonths(2), now()->subDay(), ['is_active' => true, 'approved_at' => now()->subMonth()]);
        $profile->stats()->update([
            'is_featured' => true,
            'featured_until' => now()->subDay(),
            'is_homepage_featured' => true,
            'homepage_featured_until' => now()->subDay(),
            'is_top_search' => true,
            'top_search_until' => now()->subDay(),
            'is_top_category' => true,
            'top_category_until' => now()->subDay(),
            'is_top_subcategory' => true,
            'top_subcategory_until' => now()->subDay(),
        ]);

        Artisan::call('subscriptions:expire');
        Artisan::call('placements:expire');

        $this->assertFalse($provider->subscriptions()->first()->is_active);
        $stats = $profile->stats()->first();
        $this->assertFalse($stats->is_featured);
        $this->assertFalse($stats->is_homepage_featured);
        $this->assertFalse($stats->is_top_search);
        $this->assertFalse($stats->is_top_category);
        $this->assertFalse($stats->is_top_subcategory);
    }

    public function test_review_constraints_and_stats_recalculation(): void
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider);
        $reviewer = $this->user('user');

        Review::create([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'rating' => 5,
            'status' => ReviewStatus::APPROVED,
            'comment' => 'Great',
        ]);

        app(ProfileStatsService::class)->recalculate($profile);
        $this->assertSame(1, $profile->stats()->first()->reviews_count);
        $this->assertSame('5.0', (string) $profile->stats()->first()->rating_avg);

        $this->expectException(QueryException::class);
        Review::create([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'rating' => 4,
            'status' => ReviewStatus::APPROVED,
        ]);
    }

    public function test_public_frontend_routes_render_real_data_without_public_query_debug_output(): void
    {
        $provider = $this->discoverableProvider('frontend', ['is_homepage_featured' => true, 'homepage_featured_until' => now()->addDay()]);
        $profile = $provider['profile'];

        foreach ([
            '/',
            '/search?per_page=5',
            '/category/'.$this->category->slug.'?per_page=5',
            '/subcategory/'.$this->subcategory->slug.'?per_page=5',
            '/city/'.$this->city->slug.'?per_page=5',
            '/providers/'.$profile->slug,
        ] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertDontSee('Query count')
                ->assertDontSee('Duplicate queries');
        }
    }

    public function test_admin_provider_save_does_not_overwrite_provider_owned_profile_fields(): void
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider, [
            'business_name' => 'Provider Owned Name',
            'bio' => 'Provider owned bio',
            'phone' => '+218911111111',
            'whatsapp' => '+218922222222',
        ]);

        $payload = [
            'subscription' => [
                'plan_id' => $this->plan->id,
                'starts_at' => now()->addDay()->toDateString(),
                'ends_at' => now()->addMonth()->toDateString(),
                'is_active' => false,
            ],
            'marketplace' => [],
        ];

        UserResource::saveProviderTabs($provider, $payload);

        $this->assertSame('Provider Owned Name', $profile->refresh()->business_name);
        $this->assertSame('Provider owned bio', $profile->bio);
        $this->assertSame('+218911111111', $profile->phone);
        $this->assertSame('+218922222222', $profile->whatsapp);

        ProviderResource::saveProviderData($provider->refresh(), [
            'subscription' => [
                'plan_id' => $this->plan->id,
                'starts_at' => now()->addMonths(2)->toDateString(),
                'ends_at' => now()->addMonths(3)->toDateString(),
                'is_active' => false,
            ],
            'marketplace' => [],
        ]);

        $this->assertSame('Provider Owned Name', $profile->refresh()->business_name);
        $this->assertSame('Provider owned bio', $profile->bio);
        $this->assertSame('+218911111111', $profile->phone);
        $this->assertSame('+218922222222', $profile->whatsapp);
    }

    public function test_whatsapp_numbers_must_use_wa_me_digits_only_format(): void
    {
        $rules = [
            'whatsapp' => ['required', 'string', 'max:15', 'regex:/^[1-9][0-9]{7,14}$/'],
        ];

        $this->assertFalse(validator(['whatsapp' => '218910000000'], $rules)->fails());

        foreach ([
            '+218910000000',
            '218 91 000 0000',
            '0910000000',
            'https://wa.me/218910000000',
            '218-910000000',
            '<script>alert(1)</script>',
            '00000000',
            '218',
        ] as $invalidWhatsapp) {
            $this->assertTrue(
                validator(['whatsapp' => $invalidWhatsapp], $rules)->fails(),
                "Expected [{$invalidWhatsapp}] to fail WhatsApp validation."
            );
        }
    }

    public function test_provider_types_are_cms_driven_and_filter_search_results(): void
    {
        ProviderType::create([
            'code' => 'craftsman',
            'name' => 'Craftsman',
            'name_ar' => 'حرفي',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        ProviderType::create([
            'code' => 'hidden-type',
            'name' => 'Hidden Type',
            'name_ar' => 'نوع مخفي',
            'sort_order' => 6,
            'is_active' => false,
        ]);

        app()->setLocale('ar');

        $this->assertSame('حرفي', ProviderType::options()['craftsman'] ?? null);
        $this->assertArrayNotHasKey('hidden-type', ProviderType::options());

        $expected = $this->discoverableProvider('craftsman-provider', [], [
            'provider_type' => 'craftsman',
        ])['profile'];

        $unexpected = $this->discoverableProvider('company-provider', [], [
            'provider_type' => 'company',
        ])['profile'];

        $this->assertSearchContains(['provider_type' => 'craftsman'], $expected, $unexpected);
    }

    public function test_provider_portfolio_mvp_limits_are_enforced(): void
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider);

        $first = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'First service',
            'short_description' => 'First short description',
            'is_active' => true,
        ]);

        PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'Second service',
            'short_description' => 'Second short description',
            'is_active' => true,
        ]);

        try {
            PortfolioItem::create([
                'profile_id' => $profile->id,
                'title' => 'Third service',
                'short_description' => 'Third short description',
                'is_active' => true,
            ]);
            $this->fail('Expected providers to be limited to two portfolio items.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        for ($i = 1; $i <= 4; $i++) {
            PortfolioImage::create([
                'portfolio_item_id' => $first->id,
                'path' => "portfolio/test-{$i}.jpg",
                'alt' => "Image {$i}",
                'sort_order' => $i,
            ]);
        }

        $this->expectException(ValidationException::class);

        PortfolioImage::create([
            'portfolio_item_id' => $first->id,
            'path' => 'portfolio/test-5.jpg',
            'alt' => 'Image 5',
            'sort_order' => 5,
        ]);
    }

    public function test_image_service_stores_random_webp_paths_and_rejects_oversized_uploads(): void
    {
        Storage::fake('public');

        $service = app(ProfileImageService::class);

        $avatarPath = $service->storeAvatar(UploadedFile::fake()->image('avatar.png', 800, 800)->size(1900));
        $coverPath = $service->storeGalleryImage(UploadedFile::fake()->image('cover.jpg', 1800, 1200)->size(3900));
        $portfolioPath = $service->storePortfolioImage(UploadedFile::fake()->image('work.jpg', 1200, 900)->size(3900));

        foreach ([$avatarPath, $coverPath, $portfolioPath] as $path) {
            $this->assertStringEndsWith('.webp', $path);
            $this->assertMatchesRegularExpression('/^[a-z\/]+\/[a-f0-9-]+\.webp$/', $path);
            Storage::disk('public')->assertExists($path);
        }

        $this->assertStringStartsWith('profiles/avatars/', $avatarPath);
        $this->assertStringStartsWith('profiles/covers/', $coverPath);
        $this->assertStringStartsWith('portfolio/images/', $portfolioPath);

        $service->deleteImage($avatarPath);
        Storage::disk('public')->assertMissing($avatarPath);

        $this->expectException(ValidationException::class);
        $service->storeAvatar(UploadedFile::fake()->image('too-large.png', 800, 800)->size(2100));
    }

    public function test_provider_credentials_are_publicly_visible(): void
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider, ['slug' => 'credential-owner']);
        $this->activeSubscription($provider);
        $credential = ProviderCredential::create([
            'profile_id' => $profile->id,
            'title' => 'Certified Specialist',
            'issuer' => 'Delni Academy',
            'verification_url' => 'https://verify.example.test/certified-specialist',
            'issue_date' => now()->toDateString(),
            'notes' => 'Visible credential',
        ]);

        $this->get('/providers/'.$profile->slug)
            ->assertOk()
            ->assertSee('Certified Specialist')
            ->assertSee('Delni Academy');
    }

    public function test_marketplace_placement_routes_are_admin_only_and_public_output_hides_raw_flags(): void
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider, ['slug' => 'placement-hidden']);
        $this->activeSubscription($provider);
        $profile->stats()->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => now()->addDay(),
            'is_top_search' => true,
            'top_search_until' => now()->addDay(),
            'is_top_category' => true,
            'top_category_until' => now()->addDay(),
        ]);

        $this->assertContains(
            $this->get('/cp/admin/marketplace-placements')->getStatusCode(),
            [302, 403],
        );

        $this->assertContains(
            $this->actingAs($provider)->get('/cp/admin/marketplace-placements')->getStatusCode(),
            [302, 403],
        );

        $this->actingAs($this->user('super_admin'))
            ->get('/cp/admin/marketplace-placements')
            ->assertOk();

        $this->get('/providers/'.$profile->slug)
            ->assertOk()
            ->assertDontSee('is_homepage_featured')
            ->assertDontSee('top_search_until')
            ->assertDontSee('top_category_until');
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
        $profile = Profile::firstOrNew(['user_id' => $user->id]);
        $profile->fill(array_merge([
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
        $profile->save();

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

    private function activeSubscription(User $user): Subscription
    {
        return $this->subscription($user, now(), now()->addMonth(), [
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => null,
        ]);
    }

    private function discoverableProvider(string $slug, array $stats = [], array $profileAttributes = []): array
    {
        $user = $this->user('provider', ['email' => $slug.'@example.test']);
        $profile = $this->profile($user, array_merge([
            'business_name' => 'Provider '.$slug,
            'slug' => $slug,
            'bio' => 'Provider '.$slug.' searchable bio',
        ], $profileAttributes));
        $profile->stats()->update($stats);
        $this->activeSubscription($user);

        return ['user' => $user, 'profile' => $profile];
    }

    private function assertSearchContains(array $filters, Profile $expected, ?Profile $unexpected): void
    {
        $results = app(ProfileSearchService::class)
            ->search(ProfileSearchFilters::fromArray($filters))
            ->getCollection();

        $this->assertTrue($results->contains('id', $expected->id));

        if ($unexpected !== null) {
            $this->assertFalse($results->contains('id', $unexpected->id));
        }
    }

    // MVP Visibility & Access Control Tests

    public function test_inactive_parent_category_hides_subcategory(): void
    {
        $category = Category::create([
            'name' => 'Services',
            'name_ar' => 'خدمات',
            'slug' => 'services-mvp-test',
            'is_active' => true,
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Custom Service',
            'name_ar' => 'خدمة مخصصة',
            'slug' => 'custom-service-mvp',
            'is_active' => true,
        ]);

        $this->get("/subcategory/{$subcategory->slug}")
            ->assertOk();

        $category->update(['is_active' => false]);

        $this->get("/subcategory/{$subcategory->slug}")
            ->assertNotFound();
    }
}
