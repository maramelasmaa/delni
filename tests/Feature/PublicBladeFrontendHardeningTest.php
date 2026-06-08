<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProviderType;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBladeFrontendHardeningTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionPlan $plan;

    private City $city;

    private Category $category;

    private Subcategory $subcategory;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('ar');
        session(['locale' => 'ar']);

        $this->plan = SubscriptionPlan::create([
            'name' => 'Public Test Plan',
            'name_ar' => 'خطة اختبار عامة',
            'duration_months' => 1,
            'price_lyd' => 100,
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
            'name' => 'Logos',
            'name_ar' => 'شعارات',
            'slug' => 'logos',
            'is_active' => true,
        ]);
    }

    public function test_public_pages_render_without_debug_or_internal_fields(): void
    {
        $profile = $this->discoverableProvider('safe-provider');

        foreach ([
            route('home'),
            route('public.search'),
            route('public.category', $this->category),
            route('public.subcategory', $this->subcategory),
            route('public.city', $this->city),
            route('public.provider', $profile),
            route('privacy'),
            route('terms'),
            route('disclaimer'),
            route('login'),
            route('register'),
        ] as $url) {
            $response = $this->get($url);

            $response->assertOk();
            $response->assertDontSee('Query count');
            $response->assertDontSee('Duplicate queries');
            $response->assertDontSee('is_featured');
            $response->assertDontSee('is_top_search');
            $response->assertDontSee('payment_reference');
            $response->assertDontSee('is_suspended');
            $response->assertDontSee('messages.');
            $response->assertDontSee('ProviderProfileResource');
            $response->assertDontSee('English');
        }
    }

    public function test_provider_detail_uses_logo_field_and_survives_missing_optional_public_data(): void
    {
        $profile = $this->discoverableProvider('missing-optional-data', [
            'business_name' => 'مزود بدون بيانات اختيارية',
            'logo' => null,
            'cover_image' => null,
            'bio' => null,
            'service_area_note' => null,
            'map_url' => null,
        ]);

        $this->get(route('public.provider', $profile))
            ->assertOk()
            ->assertSee('مزود بدون بيانات اختيارية')
            ->assertSee(__('messages.public.no_reviews'), false)
            ->assertDontSee('logo_path')
            ->assertDontSee('ProviderProfileResource')
            ->assertDontSee('Query count');
    }

    public function test_public_search_renders_provider_type_options_without_filament_coupling(): void
    {
        ProviderType::create([
            'code' => 'craftsman',
            'name' => 'Craftsman',
            'name_ar' => 'حرفي',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->discoverableProvider('craftsman-provider', ['provider_type' => 'craftsman']);

        $this->get(route('public.search'))
            ->assertOk()
            ->assertSee('حرفي')
            ->assertSee('value="craftsman"', false)
            ->assertDontSee('ProviderProfileResource');
    }

    public function test_review_submission_and_flagging_controls_are_policy_gated(): void
    {
        $profile = $this->discoverableProvider('reviewable-provider');
        $reviewer = $this->publicUser('reviewer@example.test');
        $otherUser = $this->publicUser('flagger@example.test');

        $review = Review::create([
            'profile_id' => $profile->id,
            'user_id' => $reviewer->id,
            'rating' => 5,
            'status' => ReviewStatus::APPROVED,
            'comment' => 'مراجعة عامة آمنة.',
        ]);

        $this->actingAs($otherUser)
            ->get(route('public.provider', $profile))
            ->assertOk()
            ->assertSee(route('review.store', $profile), false)
            ->assertSee(route('reviews.flag', $review), false)
            ->assertSee('مراجعة عامة آمنة.');
    }

    public function test_arabic_only_locale_and_custom_404_render_without_raw_keys(): void
    {
        $this->discoverableProvider('arabic-provider');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('ابحث عن متخصصين موثوقين')
            ->assertDontSee('messages.')
            ->assertDontSee('Query count')
            ->assertDontSee('Ø')
            ->assertDontSee('Ù');

        $this->get(route('locale.switch', 'en'))
            ->assertRedirect();

        $this->assertSame('ar', session('locale'));

        $this->get('/definitely-missing-public-page')
            ->assertNotFound()
            ->assertSee('404')
            ->assertDontSee('Exception')
            ->assertDontSee('Stack trace')
            ->assertDontSee('messages.');
    }

    private function discoverableProvider(string $slug, array $attributes = []): Profile
    {
        $user = User::factory()->create([
            'name' => 'مزود '.$slug,
            'email' => $slug.'@example.test',
            'is_active' => true,
            'is_suspended' => false,

        ]);
        $user->assignRole('provider');

        $profile = Profile::create(array_merge([
            'user_id' => $user->id,
            'business_name' => 'مزود '.$slug,
            'type' => 'business',
            'provider_type' => 'company',
            'bio' => 'نبذة عامة عن '.$slug,
            'slug' => $slug,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'phone' => '218910000000',
            'whatsapp' => '218910000000',
            'is_complete' => true,
            'offers_remote_work' => false,
        ], $attributes));

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

        Subscription::withoutEvents(fn (): Subscription => Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $this->plan->id,
            'starts_at' => now()->subDay()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
            'is_active' => true,
            'approved_at' => now(),
        ]));

        return $profile;
    }

    private function publicUser(string $email): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'is_active' => true,
            'is_suspended' => false,

        ]);
        $user->assignRole('user');

        return $user;
    }
}
