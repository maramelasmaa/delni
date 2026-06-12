<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderPageVisibilityTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------

    /**
     * Create a complete, visible provider profile with an active subscription.
     */
    private function makeVisibleProfile(): Profile
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);
        $user->assignRole('provider');

        $profile = Profile::factory()->complete()->create([
            'user_id' => $user->id,
        ]);

        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        $plan = SubscriptionPlan::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'is_active' => true,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        return $profile->fresh();
    }

    // -------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------

    public function test_visible_provider_profile_renders_200(): void
    {
        $profile = $this->makeVisibleProfile();

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(200);
    }

    public function test_incomplete_profile_returns_404(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'is_complete' => false,
        ]);

        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        $plan = SubscriptionPlan::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'is_active' => true,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(404);
    }

    public function test_suspended_user_profile_returns_404(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => true,
        ]);
        $user->assignRole('provider');

        $profile = Profile::factory()->complete()->create([
            'user_id' => $user->id,
        ]);

        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        $plan = SubscriptionPlan::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'is_active' => true,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonth()->toDateString(),
        ]);

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(404);
    }

    public function test_inactive_user_profile_returns_404(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
            'is_suspended' => false,
        ]);

        $profile = Profile::factory()->complete()->create([
            'user_id' => $user->id,
        ]);

        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(404);
    }

    public function test_expired_subscription_returns_404(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);
        $user->assignRole('provider');

        $profile = Profile::factory()->complete()->create([
            'user_id' => $user->id,
        ]);

        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        $plan = SubscriptionPlan::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'is_active' => false,
            'starts_at' => now()->subMonth()->toDateString(),
            'ends_at' => now()->subDay()->toDateString(),
        ]);

        $this->get(route('public.provider', $profile->slug))
            ->assertStatus(404);
    }
}
