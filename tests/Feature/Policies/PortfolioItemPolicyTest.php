<?php

namespace Tests\Feature\Policies;

use App\Models\Category;
use App\Models\City;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\ProviderCreationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PortfolioItemPolicyTest extends TestCase
{
    use RefreshDatabase;

    private City $city;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->city = City::create(['name' => 'Tripoli', 'name_ar' => 'طرابلس', 'slug' => 'tripoli', 'is_active' => true]);
        $this->category = Category::create(['name' => 'Design', 'name_ar' => 'تصميم', 'slug' => 'design', 'is_active' => true]);
    }

    public function test_provider_can_create_portfolio_items()
    {
        $provider = $this->user('provider');

        $this->assertTrue($this->can($provider, 'create', PortfolioItem::class));
    }

    public function test_non_provider_cannot_create_portfolio_items()
    {
        $regular = $this->user('user');

        $this->assertFalse($this->can($regular, 'create', PortfolioItem::class));
    }

    public function test_provider_without_profile_cannot_create_portfolio_items()
    {
        $provider = $this->user('provider');
        $provider->profile()->delete();

        $this->assertFalse($this->can($provider, 'create', PortfolioItem::class));
    }

    public function test_provider_can_view_own_portfolio_items()
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider);
        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'My Work',
            'short_description' => 'Test',
            'is_active' => false,
        ]);

        $this->assertTrue($this->can($provider, 'view', $item));
    }

    public function test_provider_can_view_own_inactive_portfolio_items()
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider);
        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'My Work',
            'short_description' => 'Test',
            'is_active' => false,
        ]);

        $this->assertTrue($this->can($provider, 'view', $item));
    }

    public function test_provider_cannot_view_other_providers_portfolio_items()
    {
        $provider1 = $this->user('provider', ['email' => 'provider1@test.com']);
        $provider2 = $this->user('provider', ['email' => 'provider2@test.com']);

        $profile1 = $this->profile($provider1);
        $item = PortfolioItem::create([
            'profile_id' => $profile1->id,
            'title' => 'Provider 1 Work',
            'short_description' => 'Test',
            'is_active' => true,
        ]);

        $this->assertFalse($this->can($provider2, 'view', $item));
    }

    public function test_guest_can_view_active_portfolio_items_on_discoverable_profile()
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider, ['is_complete' => true]);
        $this->activeSubscription($provider);

        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'Public Work',
            'short_description' => 'Test',
            'is_active' => true,
        ]);

        $this->assertTrue($this->can(null, 'view', $item));
    }

    public function test_guest_cannot_view_inactive_portfolio_items()
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider, ['is_complete' => true]);
        $this->activeSubscription($provider);

        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'Hidden Work',
            'short_description' => 'Test',
            'is_active' => false,
        ]);

        $this->assertFalse($this->can(null, 'view', $item));
    }

    public function test_guest_cannot_view_portfolio_items_on_undiscoverable_profile()
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider, ['is_complete' => true]);
        // No active subscription, so not discoverable

        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'Hidden Work',
            'short_description' => 'Test',
            'is_active' => true,
        ]);

        $this->assertFalse($this->can(null, 'view', $item));
    }

    public function test_provider_can_update_own_portfolio_items()
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider);
        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'My Work',
            'short_description' => 'Test',
        ]);

        $this->assertTrue($this->can($provider, 'update', $item));
    }

    public function test_provider_cannot_update_other_providers_portfolio_items()
    {
        $provider1 = $this->user('provider', ['email' => 'provider1@test.com']);
        $provider2 = $this->user('provider', ['email' => 'provider2@test.com']);

        $profile1 = $this->profile($provider1);
        $item = PortfolioItem::create([
            'profile_id' => $profile1->id,
            'title' => 'Provider 1 Work',
            'short_description' => 'Test',
        ]);

        $this->assertFalse($this->can($provider2, 'update', $item));
    }

    public function test_provider_cannot_update_without_provider_role()
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider);
        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'My Work',
            'short_description' => 'Test',
        ]);

        // Remove provider role
        $provider->syncRoles([]);

        $this->assertFalse($this->can($provider, 'update', $item));
    }

    public function test_provider_can_delete_own_portfolio_items()
    {
        $provider = $this->user('provider');
        $profile = $this->profile($provider);
        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'My Work',
            'short_description' => 'Test',
        ]);

        $this->assertTrue($this->can($provider, 'delete', $item));
    }

    public function test_provider_cannot_delete_other_providers_portfolio_items()
    {
        $provider1 = $this->user('provider', ['email' => 'provider1@test.com']);
        $provider2 = $this->user('provider', ['email' => 'provider2@test.com']);

        $profile1 = $this->profile($provider1);
        $item = PortfolioItem::create([
            'profile_id' => $profile1->id,
            'title' => 'Provider 1 Work',
            'short_description' => 'Test',
        ]);

        $this->assertFalse($this->can($provider2, 'delete', $item));
    }

    public function test_admin_can_view_all_portfolio_items()
    {
        $admin = $this->user('super_admin');
        $provider = $this->user('provider', ['email' => 'provider@test.com']);
        $profile = $this->profile($provider);
        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'Provider Work',
            'short_description' => 'Test',
            'is_active' => false,
        ]);

        $this->assertTrue($this->can($admin, 'view', $item));
    }

    public function test_admin_can_update_all_portfolio_items()
    {
        $admin = $this->user('super_admin');
        $provider = $this->user('provider', ['email' => 'provider@test.com']);
        $profile = $this->profile($provider);
        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'Provider Work',
            'short_description' => 'Test',
        ]);

        $this->assertTrue($this->can($admin, 'update', $item));
    }

    public function test_admin_can_delete_all_portfolio_items()
    {
        $admin = $this->user('super_admin');
        $provider = $this->user('provider', ['email' => 'provider@test.com']);
        $profile = $this->profile($provider);
        $item = PortfolioItem::create([
            'profile_id' => $profile->id,
            'title' => 'Provider Work',
            'short_description' => 'Test',
        ]);

        $this->assertTrue($this->can($admin, 'delete', $item));
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_suspended' => false,
        ], $attributes));

        $user->assignRole($role);

        if ($role === 'provider') {
            app(ProviderCreationService::class)->createProfileForUser($user);
        }

        return $user;
    }

    private function profile(User $user, array $attributes = []): Profile
    {
        $profile = Profile::firstOrNew(['user_id' => $user->id]);
        $profile->fill(array_merge([
            'user_id' => $user->id,
            'business_name' => 'Business '.$user->id,
            'bio' => 'Bio',
            'slug' => 'business-'.$user->id,
            'city_id' => $this->city->id,
            'category_id' => $this->category->id,
            'whatsapp' => '+218911234567',
            'phone' => '+218911234567',
            'is_complete' => false,
        ], $attributes));
        $profile->save();

        $profile->stats()->updateOrCreate([], [
            'rating_avg' => 0,
            'reviews_count' => 0,
            'is_top_rated' => false,
            'is_featured' => false,
        ]);

        return $profile;
    }

    private function activeSubscription(User $user): void
    {
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => SubscriptionPlan::create([
                'name' => 'Test Plan',
                'name_ar' => 'خطة',
                'duration_months' => 1,
                'price_lyd' => 100,
                'is_active' => true,
            ])->id,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
            'approved_at' => now(),
        ]);
    }

    private function can(?User $user, string $ability, string|object $resource): bool
    {
        if ($user) {
            return $user->can($ability, $resource);
        }

        // For guest case, we need to use Gate directly since policies accept ?User
        return Gate::check($ability, $resource);
    }
}
