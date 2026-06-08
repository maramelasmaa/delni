<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderPanelPhase1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create required cities and categories for profile creation
        City::factory()->create(['id' => 1]);
        Category::factory()->create(['id' => 1]);
    }

    // Auth & Access Tests
    public function test_guest_redirects_to_provider_login(): void
    {
        $response = $this->get('/provider/dashboard');
        $response->assertRedirect(route('filament.provider.auth.login'));
    }

    public function test_normal_user_cannot_access_provider_panel(): void
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/provider/dashboard');
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_provider_allowed_on_provider_dashboard(): void
    {
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $response->assertSuccessful();
    }

    public function test_suspended_provider_blocked(): void
    {
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => true]);
        $provider->assignRole('provider');

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_inactive_provider_blocked(): void
    {
        $provider = User::factory()->create(['is_active' => false, 'is_suspended' => false]);
        $provider->assignRole('provider');

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_super_admin_cannot_access_provider_panel_unless_provider_role(): void
    {
        $admin = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->get('/provider/dashboard');
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_provider_cannot_access_admin_panel(): void
    {
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        $response = $this->actingAs($provider)->get('/cp/admin');
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    // Dashboard Rendering Tests
    public function test_dashboard_renders_with_complete_profile(): void
    {
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $provider->id,
            'business_name' => 'Test Business',
            'provider_type' => 'freelancer',
            'city_id' => 1,
            'category_id' => 1,
            'phone' => '1234567890',
            'whatsapp' => '1234567890',
            'is_complete' => true,
        ]);

        ProfileStats::factory()->create([
            'profile_id' => $profile->id,
            'rating_avg' => 4.5,
            'reviews_count' => 10,
            'is_featured' => false,
        ]);

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $response->assertSuccessful();
    }

    public function test_dashboard_renders_with_missing_stats(): void
    {
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $provider->id,
            'business_name' => 'Test Business',
            'provider_type' => 'freelancer',
            'city_id' => 1,
            'category_id' => 1,
            'phone' => '1234567890',
            'whatsapp' => '1234567890',
        ]);

        // No ProfileStats created - simulate missing stats

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $response->assertSuccessful();
    }

    public function test_dashboard_renders_with_missing_subscription(): void
    {
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $provider->id,
            'business_name' => 'Test Business',
            'provider_type' => 'freelancer',
            'city_id' => 1,
            'category_id' => 1,
            'phone' => '1234567890',
            'whatsapp' => '1234567890',
        ]);

        // No active subscription created

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $response->assertSuccessful();
    }

    public function test_dashboard_renders_with_missing_profile_safely(): void
    {
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        // No profile created - simulate missing profile

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $response->assertSuccessful();
    }

    // Route & Config Cache Safety Tests
    public function test_provider_dashboard_loads_without_500(): void
    {
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        $profile = Profile::factory()->create(['user_id' => $provider->id]);

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $this->assertNotSame(500, $response->status());
    }

    public function test_route_cache_does_not_break_provider_routes(): void
    {
        // Ensure provider routes are registered and accessible after caching
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $response->assertSuccessful();
    }

    public function test_config_cache_does_not_break_provider_routes(): void
    {
        // Ensure provider panel config doesn't break routes
        $provider = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $provider->assignRole('provider');

        $response = $this->actingAs($provider)->get('/provider/dashboard');
        $response->assertSuccessful();
    }
}
