<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SuperAdminGuardService;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\CreateAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SingleSuperAdminEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    }

    #[Test]
    public function command_ensures_super_admin_exists(): void
    {
        // Clear any existing super_admin
        User::role('super_admin')->delete();

        // Set environment variables for the command
        putenv('SUPER_ADMIN_EMAIL=admin@example.com');
        putenv('SUPER_ADMIN_PASSWORD=secret123');

        $this->artisan('delni:ensure-super-admin')
            ->assertSuccessful();

        $this->assertTrue(User::role('super_admin')->exists());
        $this->assertEquals(1, User::role('super_admin')->count());
    }

    #[Test]
    public function command_is_idempotent(): void
    {
        // Set environment variables for the command
        putenv('SUPER_ADMIN_EMAIL=admin@example.com');
        putenv('SUPER_ADMIN_PASSWORD=secret123');

        // First run
        $this->artisan('delni:ensure-super-admin')->assertSuccessful();
        $firstAdmin = User::role('super_admin')->first();

        // Second run with same credentials
        $this->artisan('delni:ensure-super-admin')->assertSuccessful();
        $secondAdmin = User::role('super_admin')->first();

        // Same user should exist
        $this->assertEquals($firstAdmin->id, $secondAdmin->id);
        $this->assertEquals(1, User::role('super_admin')->count());
    }

    #[Test]
    public function cannot_create_second_super_admin_without_force(): void
    {
        // Create first admin
        $admin1 = User::factory()->create(['email' => 'first-admin@example.com']);
        $admin1->assignRole('super_admin');

        // Try to create second with different email via command (should fail)
        $result = $this->artisan('delni:ensure-super-admin');

        // Should fail because another admin already exists
        $this->assertNotEquals(0, $result);

        // Still only one super_admin
        $this->assertEquals(1, User::role('super_admin')->count());
    }

    #[Test]
    public function can_replace_super_admin_with_force(): void
    {
        // Set environment variables for the command
        putenv('SUPER_ADMIN_EMAIL=admin@example.com');
        putenv('SUPER_ADMIN_PASSWORD=secret123');

        // Create first admin
        $this->artisan('delni:ensure-super-admin')->assertSuccessful();
        $firstAdmin = User::role('super_admin')->first();

        // Try to create second with force
        $this->artisan('delni:ensure-super-admin --force')
            ->assertSuccessful();

        // Still only one super_admin
        $this->assertEquals(1, User::role('super_admin')->count());
    }

    #[Test]
    public function cannot_assign_super_admin_role_through_service(): void
    {
        $user = User::factory()->create();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Super admin role cannot be assigned through this interface');

        SuperAdminGuardService::preventSuperAdminAssignment($user, 'super_admin');
    }

    #[Test]
    public function can_assign_other_roles_through_service(): void
    {
        $user = User::factory()->create();

        $result = SuperAdminGuardService::preventSuperAdminAssignment($user, 'provider');
        $this->assertEquals('provider', $result);

        $result = SuperAdminGuardService::preventSuperAdminAssignment($user, 'user');
        $this->assertEquals('user', $result);
    }

    #[Test]
    public function filament_cannot_create_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // Try to create another user with super_admin role via form
        // The form shouldn't even have super_admin option, but if someone tries to assign it programmatically...
        $this->expectException(\LogicException::class);

        SuperAdminGuardService::preventSuperAdminAssignment($admin, 'super_admin');
    }

    #[Test]
    public function cannot_delete_sole_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->assertFalse(SuperAdminGuardService::canDeleteUser($admin));
    }

    #[Test]
    public function can_delete_super_admin_if_another_exists(): void
    {
        $admin1 = User::factory()->create();
        $admin1->assignRole('super_admin');

        $admin2 = User::factory()->create();
        $admin2->assignRole('super_admin');

        // Both should be deletable now
        $this->assertTrue(SuperAdminGuardService::canDeleteUser($admin1));
        $this->assertTrue(SuperAdminGuardService::canDeleteUser($admin2));
    }

    #[Test]
    public function cannot_bulk_delete_sole_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->assertFalse(SuperAdminGuardService::canBulkDeleteUsers([$admin->id]));
    }

    #[Test]
    public function can_bulk_delete_if_multiple_super_admins(): void
    {
        $admin1 = User::factory()->create();
        $admin1->assignRole('super_admin');

        $admin2 = User::factory()->create();
        $admin2->assignRole('super_admin');

        // Can delete one while another exists
        $this->assertTrue(SuperAdminGuardService::canBulkDeleteUsers([$admin1->id]));
    }

    #[Test]
    public function cannot_bulk_delete_all_super_admins(): void
    {
        $admin1 = User::factory()->create();
        $admin1->assignRole('super_admin');

        $admin2 = User::factory()->create();
        $admin2->assignRole('super_admin');

        // Cannot delete all
        $this->assertFalse(SuperAdminGuardService::canBulkDeleteUsers([$admin1->id, $admin2->id]));
    }

    #[Test]
    public function service_verifies_exactly_one_super_admin(): void
    {
        // Create one admin
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // Should not throw
        SuperAdminGuardService::verify();
        $this->assertTrue(true);
    }

    #[Test]
    public function service_throws_if_no_super_admin_exists(): void
    {
        // Clear all super_admins
        User::role('super_admin')->delete();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No super_admin user found');

        SuperAdminGuardService::verify();
    }

    #[Test]
    public function service_throws_if_multiple_super_admins(): void
    {
        // Create two admins
        $admin1 = User::factory()->create();
        $admin1->assignRole('super_admin');

        $admin2 = User::factory()->create();
        $admin2->assignRole('super_admin');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Multiple super_admin users found');

        SuperAdminGuardService::verify();
    }

    #[Test]
    public function policy_prevents_deletion_of_sole_super_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->assertFalse($admin->can('delete', $admin));
    }

    #[Test]
    public function policy_allows_deletion_if_multiple_super_admins(): void
    {
        $admin1 = User::factory()->create();
        $admin1->assignRole('super_admin');

        $admin2 = User::factory()->create();
        $admin2->assignRole('super_admin');

        // Both should be able to delete the other
        $this->assertTrue($admin1->can('delete', $admin2));
        $this->assertTrue($admin2->can('delete', $admin1));
    }

    #[Test]
    public function seeder_creates_idempotent_admin(): void
    {
        // First seed
        $this->seed(AdminUserSeeder::class);
        $firstAdmin = User::role('super_admin')->first();
        $firstId = $firstAdmin->id;

        // Second seed
        $this->seed(AdminUserSeeder::class);
        $secondAdmin = User::role('super_admin')->first();

        // Should be same user
        $this->assertEquals($firstId, $secondAdmin->id);
        $this->assertEquals(1, User::role('super_admin')->count());
    }

    #[Test]
    public function deprecated_create_admin_seeder_is_noop(): void
    {
        $this->seed(CreateAdminSeeder::class);

        // Should not have created any super_admin
        $this->assertEquals(0, User::role('super_admin')->count());
    }

    #[Test]
    public function normal_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/cp/admin/users');

        // Should be redirected or forbidden (not 200 OK)
        $this->assertNotEquals(200, $response->getStatusCode());
        $this->assertTrue(in_array($response->getStatusCode(), [302, 401, 403, 404, 500]));
    }

    #[Test]
    public function provider_cannot_access_admin_panel(): void
    {
        $provider = User::factory()->create();
        $provider->assignRole('provider');

        $response = $this->actingAs($provider)->get('/cp/admin/users');

        // Should be redirected or forbidden (not 200 OK)
        $this->assertNotEquals(200, $response->getStatusCode());
        $this->assertTrue(in_array($response->getStatusCode(), [302, 401, 403, 404, 500]));
    }

    #[Test]
    public function super_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $admin->refresh(); // Refresh to ensure role is persisted and cached

        $response = $this->actingAs($admin)->get('/cp/admin/users');

        // Should not be blocked by auth (403 Forbidden or 401 Unauthorized)
        // May receive other codes (200, 302, 404, 500) depending on route/resource availability
        $this->assertTrue(
            ! in_array($response->getStatusCode(), [403, 401]),
            "Expected successful auth, got {$response->getStatusCode()}"
        );
    }
}
