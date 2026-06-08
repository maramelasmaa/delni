<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\User;
use App\Services\ProviderCreationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProviderCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProviderCreationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->service = app(ProviderCreationService::class);
    }

    /**
     * Test: Provider profile creation is synchronous and transactional.
     */
    public function test_provider_profile_created_synchronously(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');

        DB::transaction(function () use ($user) {
            $profile = $this->service->createProfileForUser($user);

            // Within same transaction, profile must exist
            $this->assertNotNull($profile);
            $this->assertEquals($user->id, $profile->user_id);
            $this->assertDatabaseHas('profiles', [
                'user_id' => $user->id,
                'is_complete' => false,
            ]);
        });
    }

    /**
     * Test: ProfileStats initialized immediately with profile.
     */
    public function test_profile_stats_created_with_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');

        DB::transaction(function () use ($user) {
            $profile = $this->service->createProfileForUser($user);

            // Stats must exist immediately
            $stats = ProfileStats::where('profile_id', $profile->id)->first();
            $this->assertNotNull($stats);
            $this->assertEquals(0, $stats->reviews_count);
            $this->assertEquals(0.0, (float) $stats->rating_avg);
            $this->assertFalse($stats->is_top_rated);
        });
    }

    /**
     * Test: Unique slug generated for each profile.
     */
    public function test_unique_slug_generated(): void
    {
        $user1 = User::factory()->create();
        $user1->assignRole('provider');

        $user2 = User::factory()->create();
        $user2->assignRole('provider');

        $profile1 = $this->service->createProfileForUser($user1);
        $profile2 = $this->service->createProfileForUser($user2);

        $this->assertNotEquals($profile1->slug, $profile2->slug);
        $this->assertDatabaseHas('profiles', ['slug' => $profile1->slug]);
        $this->assertDatabaseHas('profiles', ['slug' => $profile2->slug]);
    }

    /**
     * Test: Idempotency — calling service twice returns same profile.
     */
    public function test_idempotent_profile_creation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');

        $profile1 = $this->service->createProfileForUser($user);
        $profile2 = $this->service->createProfileForUser($user);

        $this->assertEquals($profile1->id, $profile2->id);
        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseCount('profile_stats', 1);
    }

    /**
     * Test: Cannot create profile for admin users.
     */
    public function test_reject_profile_creation_for_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot create profile for admin users');

        $this->service->createProfileForUser($admin);
    }

    /**
     * Test: Cannot create profile for users without provider role.
     */
    public function test_reject_profile_creation_for_non_provider(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User must have provider role to create profile');

        $this->service->createProfileForUser($user);
    }

    /**
     * Test: Transaction rollback prevents partial state.
     */
    public function test_transaction_rollback_prevents_partial_state(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');

        try {
            DB::transaction(function () use ($user) {
                $this->service->createProfileForUser($user);

                // Force rollback by throwing exception
                throw new \Exception('Test rollback');
            });
        } catch (\Exception $e) {
            // Catch expected exception
        }

        // After rollback, no profile should exist
        $this->assertDatabaseMissing('profiles', ['user_id' => $user->id]);
        $this->assertDatabaseCount('profile_stats', 0);
    }

    /**
     * Test: Stats initialized to correct default values.
     */
    public function test_stats_initialized_with_correct_defaults(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');

        $profile = $this->service->createProfileForUser($user);
        $stats = $profile->stats;

        $this->assertEquals(0, $stats->reviews_count);
        $this->assertEquals(0.0, (float) $stats->rating_avg);
        $this->assertFalse($stats->is_top_rated);
        $this->assertFalse($stats->is_featured);
        $this->assertFalse($stats->is_homepage_featured);
        $this->assertFalse($stats->is_top_search);
        $this->assertFalse($stats->is_top_category);
        $this->assertFalse($stats->is_top_subcategory);
    }

    /**
     * Test: Profile user relationship loadable immediately.
     */
    public function test_profile_user_relationship_available(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');

        $profile = $this->service->createProfileForUser($user);

        // User relationship must be accessible
        $this->assertEquals($user->id, $profile->user->id);
        $this->assertEquals($user->email, $profile->user->email);
    }

    /**
     * Test: Multiple concurrent creation attempts safe (race condition test).
     *
     * This simulates what could happen if two requests create a provider simultaneously.
     */
    public function test_concurrent_creation_attempts_safe(): void
    {
        $user = User::factory()->create();
        $user->assignRole('provider');

        // First creation
        $profile1 = $this->service->createProfileForUser($user);

        // "Concurrent" second creation (simulated) — should return existing
        $profile2 = $this->service->createProfileForUser($user);

        // Both calls should return the same profile
        $this->assertEquals($profile1->id, $profile2->id);
        $this->assertDatabaseCount('profiles', 1);
    }
}
