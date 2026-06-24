<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\OnboardingToken;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Review;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\UserFavorite;
use App\Services\ProfileStatsService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HostileAuditTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Category $category;

    private Subcategory $subcategory;

    private City $city;

    private User $publicUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset all guard singletons so actingAs() in one test cannot bleed into the next.
        Auth::forgetGuards();

        Cache::flush();

        Role::findOrCreate('provider', 'web');
        Role::findOrCreate('user', 'web');
        Role::findOrCreate('super_admin', 'web');

        $this->category = Category::factory()->create(['is_active' => true, 'slug' => 'hostile-category']);
        $this->subcategory = Subcategory::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
            'slug' => 'hostile-subcategory',
        ]);
        $this->city = City::factory()->create(['is_active' => true, 'slug' => 'hostile-city']);
        $this->publicUser = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $this->publicUser->assignRole('user');
    }

    public function test_hidden_provider_matrix_does_not_leak_across_api_surfaces(): void
    {
        $states = [
            'active' => [],
            'inactive' => ['user' => ['is_active' => false]],
            'suspended' => ['user' => ['is_suspended' => true]],
            'expired' => ['profile' => ['provider_access_ends_at' => now()->subDay()]],
            'deleted' => ['deleted' => true],
            'incomplete' => ['profile' => ['is_complete' => false]],
        ];

        foreach ($states as $state => $overrides) {
            Cache::flush();
            $profile = $this->makeProviderProfile($state, $overrides);
            UserFavorite::create(['user_id' => $this->publicUser->id, 'profile_id' => $profile->id]);

            $shouldBeVisible = $state === 'active';

            // Check search using state name as keyword — unique to this profile's business_name.
            // Use slug-presence (not count) because profiles accumulate across iterations.
            $searchSlugs = $this->getJson('/api/v1/search?q='.$state)->assertOk()->json('data.*.slug') ?? [];
            if ($shouldBeVisible) {
                $this->assertContains($profile->slug, $searchSlugs, "search missing state={$state}");
            } else {
                $this->assertNotContains($profile->slug, $searchSlugs, "search leakage state={$state}");
            }

            // For category/subcategory/top-rated/home: profiles accumulate across iterations,
            // so use slug-presence instead of total-count assertions.
            foreach ([
                "/api/v1/categories/{$this->category->slug}" => 'data.providers.*.slug',
                "/api/v1/subcategories/{$this->subcategory->slug}" => 'data.providers.*.slug',
                '/api/v1/top-rated' => 'data.*.slug',
            ] as $uri => $slugPath) {
                $slugs = $this->getJson($uri)->assertOk()->json($slugPath) ?? [];
                if ($shouldBeVisible) {
                    $this->assertContains($profile->slug, $slugs, "{$uri} missing state={$state}");
                } else {
                    $this->assertNotContains($profile->slug, $slugs, "{$uri} leakage state={$state}");
                }
            }

            $homeSlugs = collect($this->getJson('/api/v1/home')->assertOk()->json('data.featured_providers') ?? [])->pluck('slug')->all();
            if ($shouldBeVisible) {
                $this->assertContains($profile->slug, $homeSlugs, "home missing state={$state}");
            } else {
                $this->assertNotContains($profile->slug, $homeSlugs, "home leakage state={$state}");
            }

            $this->getJson("/api/v1/providers/{$profile->slug}")
                ->assertStatus($shouldBeVisible ? 200 : 404);

            $favSlugs = $this->actingAs($this->publicUser, 'sanctum')
                ->getJson('/api/v1/favorites')
                ->assertOk()
                ->json('data.*.slug') ?? [];
            if ($shouldBeVisible) {
                $this->assertContains($profile->slug, $favSlugs, "favorites missing state={$state}");
            } else {
                $this->assertNotContains($profile->slug, $favSlugs, "favorites leakage state={$state}");
            }
        }
    }

    public function test_token_attacks_are_rejected_for_protected_api_routes(): void
    {
        $token = $this->publicUser->createToken('mobile')->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk();

        // Reset guard singleton so cached $user from above doesn't bleed into unauthenticated checks.
        Auth::forgetGuards();

        $this->withToken('not-a-real-token')->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->withHeader('Authorization', 'Bearer badly formed token')->getJson('/api/v1/auth/me')->assertUnauthorized();

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        Auth::forgetGuards();
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();

        Auth::forgetGuards();
        $expired = $this->publicUser->createToken('expired', ['*'], now()->subHour())->plainTextToken;
        $this->withToken($expired)->getJson('/api/v1/auth/me')->assertUnauthorized();

        $otherUser = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $otherUser->assignRole('user');
        $otherToken = $otherUser->createToken('other')->plainTextToken;

        $profile = $this->makeProviderProfile('visible-for-favorites');
        UserFavorite::create(['user_id' => $this->publicUser->id, 'profile_id' => $profile->id]);

        $this->withToken($otherToken)
            ->getJson('/api/v1/favorites')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_account_and_reset_replay_attacks_are_rejected(): void
    {
        $victim = User::factory()->create(['email' => 'victim@example.test']);
        $victim->assignRole('user');

        $known = $this->postJson('/api/v1/auth/forgot-password', ['email' => $victim->email])->assertOk()->json('message');
        $unknown = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'nobody@example.test'])->assertOk()->json('message');
        $this->assertSame($known, $unknown);

        $resetToken = Password::createToken($victim);
        $payload = [
            'token' => $resetToken,
            'email' => $victim->email,
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ];

        $this->postJson('/api/v1/auth/reset-password', $payload)->assertOk();
        $this->postJson('/api/v1/auth/reset-password', $payload)->assertUnprocessable();

        $provider = User::factory()->create(['password' => Hash::make('oldPassword123')]);
        $provider->assignRole('provider');
        $plainTextSetupToken = OnboardingToken::generatePlainTextToken();
        $setupToken = OnboardingToken::create([
            'user_id' => $provider->id,
            'token' => OnboardingToken::hashToken($plainTextSetupToken),
            'expires_at' => now()->addHour(),
        ]);

        $setupPayload = [
            'token' => $plainTextSetupToken,
            'password' => 'ProviderNew123!',
            'password_confirmation' => 'ProviderNew123!',
        ];

        $this->post('/onboarding/set-password', $setupPayload)->assertRedirect();
        $this->post('/onboarding/set-password', $setupPayload)->assertSessionHasErrors('token');
    }

    public function test_review_attacks_do_not_manipulate_public_stats_or_rankings(): void
    {
        $profile = $this->makeProviderProfile('review-target');

        Review::factory()->count(5)->create([
            'profile_id' => $profile->id,
            'rating' => 5,
            'status' => ReviewStatus::APPROVED,
        ]);
        Review::factory()->create([
            'profile_id' => $profile->id,
            'rating' => 1,
            'status' => ReviewStatus::REJECTED,
        ]);
        Review::factory()->create([
            'profile_id' => $profile->id,
            'rating' => 1,
            'status' => ReviewStatus::PENDING,
        ]);

        app(ProfileStatsService::class)->recalculate($profile);

        $this->assertSame(5, $profile->stats()->first()->reviews_count);
        $this->assertSame(5.0, (float) $profile->stats()->first()->rating_avg);

        $this->getJson('/api/v1/top-rated')
            ->assertOk()
            ->assertJsonPath('data.0.slug', $profile->slug)
            ->assertJsonPath('data.0.reviews_count', 5);
        // json_encode(5.0) = "5" in PHP (whole-number float becomes integer in JSON),
        // so assert equality via (float) to avoid strict 5 !== 5.0 type mismatch.
        $ratingInJson = (float) $this->getJson('/api/v1/top-rated')->json('data.0.rating_average');
        $this->assertSame(5.0, $ratingInJson);

        $this->postJson("/api/v1/providers/{$profile->slug}/reviews", ['rating' => 5])->assertUnauthorized();

        $providerUser = $profile->user;
        $this->actingAs($providerUser, 'sanctum')
            ->postJson("/api/v1/providers/{$profile->slug}/reviews", ['rating' => 5])
            ->assertForbidden();

        $reviewer = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $reviewer->assignRole('user');

        $this->actingAs($reviewer, 'sanctum')
            ->postJson("/api/v1/providers/{$profile->slug}/reviews", ['rating' => 4])
            ->assertOk();

        $this->actingAs($reviewer, 'sanctum')
            ->postJson("/api/v1/providers/{$profile->slug}/reviews", ['rating' => 3])
            ->assertUnprocessable();

        $suspended = User::factory()->create(['is_active' => true, 'is_suspended' => true]);
        $suspended->assignRole('user');

        $this->actingAs($suspended, 'sanctum')
            ->postJson("/api/v1/providers/{$profile->slug}/reviews", ['rating' => 5])
            ->assertForbidden();
    }

    public function test_favorites_attacks_are_idempotent_and_hidden_targets_are_rejected(): void
    {
        $visible = $this->makeProviderProfile('favorite-visible');
        $expired = $this->makeProviderProfile('favorite-expired', [
            'profile' => ['provider_access_ends_at' => now()->subDay()],
        ]);
        $suspended = $this->makeProviderProfile('favorite-suspended', [
            'user' => ['is_suspended' => true],
        ]);

        $this->actingAs($this->publicUser, 'sanctum')
            ->postJson("/api/v1/favorites/{$visible->slug}")
            ->assertOk();
        $this->actingAs($this->publicUser, 'sanctum')
            ->postJson("/api/v1/favorites/{$visible->slug}")
            ->assertOk();
        $this->assertSame(1, UserFavorite::where('user_id', $this->publicUser->id)->where('profile_id', $visible->id)->count());

        $this->actingAs($this->publicUser, 'sanctum')
            ->deleteJson("/api/v1/favorites/{$visible->slug}")
            ->assertOk();
        $this->actingAs($this->publicUser, 'sanctum')
            ->deleteJson("/api/v1/favorites/{$visible->slug}")
            ->assertOk();

        $this->actingAs($this->publicUser, 'sanctum')
            ->postJson("/api/v1/favorites/{$expired->slug}")
            ->assertUnprocessable();
        $this->actingAs($this->publicUser, 'sanctum')
            ->postJson("/api/v1/favorites/{$suspended->slug}")
            ->assertUnprocessable();
    }

    private function makeProviderProfile(string $slugSuffix, array $overrides = []): Profile
    {
        $user = User::factory()->create(array_merge([
            'is_active' => true,
            'is_suspended' => false,
        ], $overrides['user'] ?? []));
        $user->assignRole('provider');

        $profile = Profile::factory()->create(array_merge([
            'user_id' => $user->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'business_name' => "Hostile {$slugSuffix}",
            'bio' => "Hostile audit {$slugSuffix}",
            'slug' => "hostile-{$slugSuffix}",
            'is_complete' => true,
            'provider_access_ends_at' => now()->addMonth(),
        ], $overrides['profile'] ?? []));

        $profile->subcategories()->sync([$this->subcategory->id]);

        ProfileStats::factory()->create([
            'profile_id' => $profile->id,
            'rating_avg' => 5.0,
            'reviews_count' => 5,
            'is_homepage_featured' => true,
            'homepage_featured_until' => now()->addDay()->toDateString(),
        ]);

        if (($overrides['deleted'] ?? false) === true) {
            $profile->delete();
        }

        return $profile->fresh(with: ['user']) ?? $profile;
    }
}
