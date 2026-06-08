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

/**
 * Integration test: Provider user creation through CreateUser page.
 *
 * This test verifies the entire flow from admin creating a provider
 * through to the provider having a complete profile and stats.
 */
class CreateProviderUserIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * Test: Admin creates provider through Filament, profile created synchronously.
     */
    public function test_admin_can_create_provider_with_immediate_profile(): void
    {
        // Simulate admin authenticated
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        // Provider data to create
        $providerData = [
            'name' => 'Test Provider',
            'email' => 'provider@test.com',
            'password' => 'SecurePassword123!',
            'phone' => '555-0100',
            'role' => 'provider',
        ];

        // Create provider through transaction (simulating Filament create page)
        $provider = DB::transaction(function () use ($providerData) {
            $user = User::create([
                'name' => $providerData['name'],
                'email' => $providerData['email'],
                'password' => bcrypt($providerData['password']),
                'phone' => $providerData['phone'],
                'is_active' => true,
            ]);

            $user->assignRole('provider');

            // Simulate what CreateUser page does
            $service = app(ProviderCreationService::class);
            $service->createProfileForUser($user);

            return $user;
        });

        // Verify provider created
        $this->assertDatabaseHas('users', [
            'email' => 'provider@test.com',
        ]);

        // Verify profile created immediately (synchronously)
        $this->assertDatabaseHas('profiles', [
            'user_id' => $provider->id,
            'is_complete' => false,
        ]);

        // Verify stats initialized
        $this->assertDatabaseHas('profile_stats', [
            'profile_id' => $provider->profile->id,
            'reviews_count' => 0,
            'rating_avg' => 0.0,
            'is_top_rated' => false,
        ]);
    }

    /**
     * Test: Provider can access their profile immediately after creation.
     */
    public function test_provider_profile_accessible_immediately(): void
    {
        // Create provider
        $provider = User::factory()->create();
        $provider->assignRole('provider');

        $service = app(ProviderCreationService::class);
        $profile = $service->createProfileForUser($provider);

        // Reload provider to ensure relationship loaded
        $provider->refresh();

        // Profile must be accessible immediately
        $this->assertNotNull($provider->profile);
        $this->assertEquals($profile->id, $provider->profile->id);
        $this->assertFalse($provider->profile->is_complete);
        $this->assertNotNull($provider->profile->stats);
        $this->assertEquals(0, $provider->profile->stats->reviews_count);
    }

    /**
     * Test: Profile creation fails if user not provider role.
     */
    public function test_profile_creation_fails_if_not_provider(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $service = app(ProviderCreationService::class);

        $this->expectException(\Exception::class);

        $service->createProfileForUser($user);
    }

    /**
     * Test: Transaction rolls back if error occurs during provider creation.
     */
    public function test_transaction_rollback_on_error(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');

        try {
            DB::transaction(function () use ($provider) {
                $service = app(ProviderCreationService::class);
                $service->createProfileForUser($provider);

                // Simulate an error during the broader transaction
                throw new \Exception('Simulated business logic error');
            });
        } catch (\Exception $e) {
            // Expected
        }

        // After rollback, profile should not exist
        $this->assertDatabaseMissing('profiles', [
            'user_id' => $provider->id,
        ]);

        $this->assertDatabaseMissing('profile_stats', [
            'profile_id' => Profile::where('user_id', $provider->id)->value('id'),
        ]);
    }

    /**
     * Test: Multiple providers created correctly with unique slugs.
     */
    public function test_multiple_providers_created_with_unique_slugs(): void
    {
        $provider1 = User::factory()->create();
        $provider1->assignRole('provider');

        $provider2 = User::factory()->create();
        $provider2->assignRole('provider');

        $service = app(ProviderCreationService::class);

        $profile1 = $service->createProfileForUser($provider1);
        $profile2 = $service->createProfileForUser($provider2);

        // Both profiles must exist with unique slugs
        $this->assertNotEquals($profile1->slug, $profile2->slug);

        // Both must have stats
        $this->assertNotNull($profile1->stats);
        $this->assertNotNull($profile2->stats);

        // Database should have 2 profiles and 2 stats records
        $this->assertDatabaseCount('profiles', 2);
        $this->assertDatabaseCount('profile_stats', 2);
    }

    /**
     * Test: Admin creation in transaction is all-or-nothing.
     */
    public function test_provider_creation_is_atomic(): void
    {
        $initialProfileCount = Profile::count();
        $initialStatsCount = ProfileStats::count();
        $initialUserCount = User::count();

        // Attempt to create provider, but rollback
        try {
            DB::transaction(function () {
                $user = User::create([
                    'name' => 'Test Provider',
                    'email' => 'test@example.com',
                    'password' => bcrypt('password'),
                ]);

                $user->assignRole('provider');

                $service = app(ProviderCreationService::class);
                $service->createProfileForUser($user);

                // Force rollback
                throw new \Exception('Forced rollback');
            });
        } catch (\Exception $e) {
            // Expected
        }

        // No new records should exist (atomic rollback)
        $this->assertEquals($initialUserCount, User::count());
        $this->assertEquals($initialProfileCount, Profile::count());
        $this->assertEquals($initialStatsCount, ProfileStats::count());
    }
}
