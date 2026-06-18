<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Provider\Resources\ProfileResource as ProviderProfileResource;
use App\Filament\Resources\ProviderResource as AdminProviderResource;
use App\Filament\Resources\ProviderResource\Pages\CreateProvider;
use App\Models\OnboardingToken;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\User;
use App\Services\MarketplaceRankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_load_create_provider_page_and_create_provider_with_profile(): void
    {
        $admin = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        Livewire::test(CreateProvider::class)
            ->assertStatus(200)
            ->fillForm([
                'name' => 'John Provider',
                'email' => 'john.provider@example.com',
                'phone' => '+218910000000',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $provider = User::where('email', 'john.provider@example.com')->first();
        $this->assertNotNull($provider);
        $this->assertTrue($provider->hasRole('provider'));

        $profile = Profile::where('user_id', $provider->id)->first();
        $this->assertNotNull($profile);

        $stats = ProfileStats::where('profile_id', $profile->id)->first();
        $this->assertNotNull($stats);

        $token = OnboardingToken::where('user_id', $provider->id)->first();
        $this->assertNotNull($token);
    }

    public function test_provider_can_visit_onboarding_link_and_set_password(): void
    {
        $admin = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin);

        Livewire::test(CreateProvider::class)
            ->fillForm([
                'name' => 'John Provider 2',
                'email' => 'john.provider2@example.com',
                'phone' => '+218910000000',
            ])
            ->call('create');

        $provider = User::where('email', 'john.provider2@example.com')->first();
        $token = OnboardingToken::where('user_id', $provider->id)->first();

        auth()->logout();

        $response = $this->get(route('onboarding.show', ['token' => $token->token]));
        $response->assertStatus(200);

        $response2 = $this->post(route('onboarding.set-password'), [
            'token' => $token->token,
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response2->assertRedirect(route('filament.provider.auth.login'));

        $this->assertNotNull($token->fresh()->used_at);
    }

    public function test_provider_profile_navigation_uses_profile_slug_instead_of_numeric_id(): void
    {
        $provider = $this->createProvider([
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $profile = $provider->profile()->firstOrFail();

        $this->actingAs($provider);

        $url = ProviderProfileResource::getNavigationUrl();

        $this->assertStringContainsString("/provider/profiles/{$profile->slug}/edit", $url);
        $this->assertStringNotContainsString("/provider/profiles/{$profile->id}/edit", $url);
    }

    public function test_admin_can_remove_homepage_featured_access_from_provider(): void
    {
        $provider = $this->createProvider([
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $profile = $provider->profile()->firstOrFail();
        $profile->stats()->update([
            'is_homepage_featured' => true,
            'homepage_featured_until' => now()->addWeek()->toDateString(),
            'is_top_search' => true,
            'top_search_until' => now()->addWeek()->toDateString(),
            'is_top_category' => true,
            'top_category_until' => now()->addWeek()->toDateString(),
            'is_top_subcategory' => true,
            'top_subcategory_until' => now()->addWeek()->toDateString(),
        ]);

        AdminProviderResource::saveProviderData($provider, [
            'marketplace' => [
                'homepage_featured' => false,
                'homepage_featured_until' => null,
            ],
        ]);

        $stats = $profile->stats()->firstOrFail()->refresh();

        $this->assertFalse($stats->is_homepage_featured);
        $this->assertNull($stats->homepage_featured_until);
        $this->assertFalse($stats->is_top_search);
        $this->assertNull($stats->top_search_until);
        $this->assertFalse($stats->is_top_category);
        $this->assertNull($stats->top_category_until);
        $this->assertFalse($stats->is_top_subcategory);
        $this->assertNull($stats->top_subcategory_until);
    }

    public function test_search_ranking_ignores_removed_top_search_placement(): void
    {
        $olderTopSearchProfile = Profile::factory()
            ->complete()
            ->withStats()
            ->create(['created_at' => now()->subDay()]);

        $newerNormalProfile = Profile::factory()
            ->complete()
            ->withStats()
            ->create(['created_at' => now()]);

        $olderTopSearchProfile->stats()->update([
            'is_top_search' => true,
            'top_search_until' => now()->addWeek()->toDateString(),
        ]);

        $ids = app(MarketplaceRankingService::class)
            ->applySearchRanking(
                Profile::query()
                    ->select('profiles.*')
                    ->join('profile_stats', 'profile_stats.profile_id', '=', 'profiles.id')
            )
            ->pluck('profiles.id')
            ->all();

        $this->assertSame($newerNormalProfile->id, $ids[0]);
        $this->assertSame($olderTopSearchProfile->id, $ids[1]);
    }

    public function test_provider_dashboard_page_loads_successfully(): void
    {
        $provider = $this->createProvider([
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $provider->profile->update([
            'provider_access_ends_at' => now()->addMonth(),
        ]);

        $this->actingAs($provider);

        $response = $this->get('/provider/dashboard');
        $response->assertStatus(200);
        $response->assertSee('لوحة التحكم');
        $response->assertSee('تعديل الملف الشخصي');
    }
}
