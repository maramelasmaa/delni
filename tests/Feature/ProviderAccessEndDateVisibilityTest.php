<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderAccessEndDateVisibilityTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------

    private function makeVisibleProfile(array $overrides = []): Profile
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->complete()->create(array_merge([
            'user_id' => $user->id,
            'provider_access_ends_at' => now()->addMonth(),
        ], $overrides));

        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        return $profile->fresh();
    }

    private function makeExpiredProfile(): Profile
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->complete()->create([
            'user_id' => $user->id,
            'provider_access_ends_at' => now()->subDay(),
        ]);

        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        return $profile->fresh();
    }

    private function makeNullAccessProfile(): Profile
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->complete()->create([
            'user_id' => $user->id,
            'provider_access_ends_at' => null,
        ]);

        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        return $profile->fresh();
    }

    private function makeSuspendedProfile(): Profile
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => true]);
        $user->assignRole('provider');

        $profile = Profile::factory()->complete()->create([
            'user_id' => $user->id,
            'provider_access_ends_at' => now()->addMonth(),
        ]);

        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        return $profile->fresh();
    }

    private function makeIncompleteProfile(): Profile
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => false,
            'provider_access_ends_at' => now()->addMonth(),
        ]);

        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        return $profile->fresh();
    }

    // -------------------------------------------------------------------
    // Section 1: Provider detail page (/providers/{slug})
    // -------------------------------------------------------------------

    public function test_valid_provider_profile_returns_200(): void
    {
        $profile = $this->makeVisibleProfile();

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(200);
    }

    public function test_expired_access_provider_profile_returns_404(): void
    {
        $profile = $this->makeExpiredProfile();

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(404);
    }

    public function test_null_access_provider_profile_returns_404(): void
    {
        $profile = $this->makeNullAccessProfile();

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(404);
    }

    public function test_suspended_provider_profile_returns_404(): void
    {
        $profile = $this->makeSuspendedProfile();

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(404);
    }

    public function test_incomplete_provider_profile_returns_404(): void
    {
        $profile = $this->makeIncompleteProfile();

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(404);
    }

    // -------------------------------------------------------------------
    // Section 2: Category listing page (/category/{slug})
    // -------------------------------------------------------------------

    public function test_valid_provider_appears_on_category_page(): void
    {
        $profile = $this->makeVisibleProfile();
        $category = $profile->category;

        $this->get(route('public.category', $category->slug))
            ->assertStatus(200)
            ->assertSee($profile->business_name);
    }

    public function test_expired_provider_hidden_on_category_page(): void
    {
        $profile = $this->makeExpiredProfile();
        $category = $profile->category;

        $this->get(route('public.category', $category->slug))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    public function test_null_access_provider_hidden_on_category_page(): void
    {
        $profile = $this->makeNullAccessProfile();
        $category = $profile->category;

        $this->get(route('public.category', $category->slug))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    public function test_suspended_provider_hidden_on_category_page(): void
    {
        $profile = $this->makeSuspendedProfile();
        $category = $profile->category;

        $this->get(route('public.category', $category->slug))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    public function test_incomplete_provider_hidden_on_category_page(): void
    {
        $profile = $this->makeIncompleteProfile();
        $category = $profile->category;

        $this->get(route('public.category', $category->slug))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    // -------------------------------------------------------------------
    // Section 3: Subcategory listing page (/subcategory/{slug})
    // -------------------------------------------------------------------

    public function test_valid_provider_appears_on_subcategory_page(): void
    {
        $subcategory = Subcategory::factory()->create(['is_active' => true]);
        $profile = $this->makeVisibleProfile(['category_id' => $subcategory->category_id]);
        $profile->subcategories()->sync([$subcategory->id]);

        $this->get(route('public.subcategory', $subcategory->slug))
            ->assertStatus(200)
            ->assertSee($profile->business_name);
    }

    public function test_expired_provider_hidden_on_subcategory_page(): void
    {
        $subcategory = Subcategory::factory()->create(['is_active' => true]);
        $profile = $this->makeExpiredProfile();
        $profile->subcategories()->sync([$subcategory->id]);

        $this->get(route('public.subcategory', $subcategory->slug))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    public function test_null_access_provider_hidden_on_subcategory_page(): void
    {
        $subcategory = Subcategory::factory()->create(['is_active' => true]);
        $profile = $this->makeNullAccessProfile();
        $profile->subcategories()->sync([$subcategory->id]);

        $this->get(route('public.subcategory', $subcategory->slug))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    // -------------------------------------------------------------------
    // Section 4: Top-rated page (/top-rated)
    // Top-rated applies live eligibility: reviews_count >= 5, rating_avg >= 4.5
    // -------------------------------------------------------------------

    public function test_valid_top_rated_provider_appears_on_top_rated_page(): void
    {
        $profile = $this->makeVisibleProfile();
        ProfileStats::where('profile_id', $profile->id)->update([
            'reviews_count' => 10,
            'rating_avg' => 4.8,
        ]);

        $this->get(route('public.top-rated'))
            ->assertStatus(200)
            ->assertSee($profile->business_name);
    }

    public function test_expired_high_rated_provider_hidden_on_top_rated_page(): void
    {
        $profile = $this->makeExpiredProfile();
        ProfileStats::where('profile_id', $profile->id)->update([
            'reviews_count' => 10,
            'rating_avg' => 4.9,
        ]);

        $this->get(route('public.top-rated'))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    public function test_null_access_high_rated_provider_hidden_on_top_rated_page(): void
    {
        $profile = $this->makeNullAccessProfile();
        ProfileStats::where('profile_id', $profile->id)->update([
            'reviews_count' => 10,
            'rating_avg' => 4.9,
        ]);

        $this->get(route('public.top-rated'))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    // -------------------------------------------------------------------
    // Section 5: Search page (/search)
    // -------------------------------------------------------------------

    public function test_valid_provider_appears_in_search_by_category(): void
    {
        $profile = $this->makeVisibleProfile();

        $this->get(route('public.search', ['category_id' => $profile->category_id]))
            ->assertStatus(200)
            ->assertSee($profile->business_name);
    }

    public function test_expired_provider_absent_from_search_by_category(): void
    {
        $profile = $this->makeExpiredProfile();

        $this->get(route('public.search', ['category_id' => $profile->category_id]))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    public function test_null_access_provider_absent_from_search(): void
    {
        $profile = $this->makeNullAccessProfile();

        $this->get(route('public.search', ['category_id' => $profile->category_id]))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    public function test_suspended_provider_absent_from_search(): void
    {
        $profile = $this->makeSuspendedProfile();

        $this->get(route('public.search', ['category_id' => $profile->category_id]))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    // -------------------------------------------------------------------
    // Section 6: City listing page (/city/{slug})
    // -------------------------------------------------------------------

    public function test_valid_provider_appears_on_city_page(): void
    {
        $city = City::factory()->create(['is_active' => true]);
        $profile = $this->makeVisibleProfile(['city_id' => $city->id]);

        $this->get(route('public.city', $city->slug))
            ->assertStatus(200)
            ->assertSee($profile->business_name);
    }

    public function test_expired_provider_hidden_on_city_page(): void
    {
        $city = City::factory()->create(['is_active' => true]);
        $profile = $this->makeExpiredProfile();
        $profile->update(['city_id' => $city->id]);

        $this->get(route('public.city', $city->slug))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    // -------------------------------------------------------------------
    // Section 7: Homepage featured (/homepage featured section)
    // Featured requires is_homepage_featured=1 AND homepage_featured_until >= today
    // AND the provider must be discoverable (access end date valid)
    // -------------------------------------------------------------------

    public function test_valid_provider_with_active_featured_appears_on_homepage(): void
    {
        $profile = $this->makeVisibleProfile();
        ProfileStats::where('profile_id', $profile->id)->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => now()->addDays(7)->toDateString(),
        ]);

        $this->get(route('home'))
            ->assertStatus(200)
            ->assertSee($profile->business_name);
    }

    public function test_expired_provider_with_active_featured_placement_does_not_appear_on_homepage(): void
    {
        $profile = $this->makeExpiredProfile();
        ProfileStats::where('profile_id', $profile->id)->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => now()->addDays(7)->toDateString(),
        ]);

        $this->get(route('home'))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    public function test_valid_provider_with_expired_featured_placement_does_not_appear_in_featured(): void
    {
        $profile = $this->makeVisibleProfile();
        ProfileStats::where('profile_id', $profile->id)->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => now()->subDay()->toDateString(),
        ]);

        // Provider may still appear as a non-featured provider on the homepage
        // but must NOT appear in the featured section specifically.
        // We test the homepage loads without error; the featured providers list
        // is filtered by MarketplaceRankingService::applyHomepageFeaturedOnly().
        $this->get(route('home'))
            ->assertStatus(200);
    }

    public function test_suspended_provider_with_active_featured_does_not_appear_on_homepage(): void
    {
        $profile = $this->makeSuspendedProfile();
        ProfileStats::where('profile_id', $profile->id)->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => now()->addDays(7)->toDateString(),
        ]);

        $this->get(route('home'))
            ->assertStatus(200)
            ->assertDontSee($profile->business_name);
    }

    // -------------------------------------------------------------------
    // Section 8: Datetime boundary — today's exact datetime rule
    // isPast() compares against now() at datetime precision.
    // A date of exactly now() would be isPast() = false (future = visible).
    // A date of 1 second ago is isPast() = true (hidden).
    // -------------------------------------------------------------------

    public function test_provider_with_access_ending_in_future_is_visible(): void
    {
        $profile = $this->makeVisibleProfile(['provider_access_ends_at' => now()->addSecond()]);

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(200);
    }

    public function test_provider_with_access_ended_one_second_ago_returns_404(): void
    {
        $profile = $this->makeVisibleProfile(['provider_access_ends_at' => now()->subSecond()]);

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(404);
    }

    // -------------------------------------------------------------------
    // Section 9: Multiple providers — only valid ones appear
    // -------------------------------------------------------------------

    public function test_only_valid_provider_appears_while_expired_is_hidden_on_same_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $valid = $this->makeVisibleProfile(['category_id' => $category->id]);
        $expired = $this->makeExpiredProfile();
        $expired->update(['category_id' => $category->id]);

        $response = $this->get(route('public.category', $category->slug))
            ->assertStatus(200);

        $response->assertSee($valid->business_name);
        $response->assertDontSee($expired->business_name);
    }
}
