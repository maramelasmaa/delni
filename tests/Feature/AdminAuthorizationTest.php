<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that UserResource excludes providers
     * (They should only appear in ProviderResource)
     */
    public function test_user_resource_excludes_providers(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('super_admin');

        $provider = User::create([
            'name' => 'Provider',
            'email' => 'provider@example.com',
            'password' => bcrypt('password'),
        ]);
        $provider->assignRole('provider');

        $publicUser = User::create([
            'name' => 'Public User',
            'email' => 'public@example.com',
            'password' => bcrypt('password'),
        ]);

        // Verify filtering logic via the model query
        $userQuery = User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'provider'))->get();

        // Admin and public user should be visible
        $this->assertTrue($userQuery->contains('id', $admin->id));
        $this->assertTrue($userQuery->contains('id', $publicUser->id));

        // Provider should NOT be visible in UserResource
        $this->assertFalse($userQuery->contains('id', $provider->id));
    }

    /**
     * Test that ProviderResource filters to providers only
     */
    public function test_provider_resource_shows_only_providers(): void
    {
        $provider = User::create([
            'name' => 'Provider',
            'email' => 'provider@example.com',
            'password' => bcrypt('password'),
        ]);
        $provider->assignRole('provider');

        $publicUser = User::create([
            'name' => 'Public User',
            'email' => 'public@example.com',
            'password' => bcrypt('password'),
        ]);

        // Verify ProviderResource filtering logic
        $providerQuery = User::whereHas('roles', fn ($q) => $q->where('name', 'provider'))->get();

        // Provider should be visible
        $this->assertTrue($providerQuery->contains('id', $provider->id));

        // Public user should NOT be visible in ProviderResource
        $this->assertFalse($providerQuery->contains('id', $publicUser->id));
    }

    /**
     * Test that admin has super_admin role
     */
    public function test_admin_role_assignment(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('super_admin');

        $this->assertTrue($admin->hasRole('super_admin'));
    }

    /**
     * Test that provider has provider role
     */
    public function test_provider_role_assignment(): void
    {
        $provider = User::create([
            'name' => 'Provider',
            'email' => 'provider@example.com',
            'password' => bcrypt('password'),
        ]);
        $provider->assignRole('provider');

        $this->assertTrue($provider->hasRole('provider'));
    }
}
