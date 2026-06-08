<?php

namespace Tests;

use App\Models\Profile;
use App\Models\User;
use App\Services\ProviderCreationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set up test environment with required seeders.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles for all tests (idempotent, safe to call multiple times)
        $this->seed(RoleSeeder::class);
    }

    /**
     * Create a user with a given role and optional profile (for providers).
     *
     * For provider users, also creates their profile synchronously.
     * Non-provider users don't get profiles.
     *
     * Usage:
     *   $provider = $this->createUserWithRole('provider');
     *   $user = $this->createUserWithRole('user');
     *
     * @param  string  $role  The role to assign (provider, user, super_admin, etc.)
     * @param  array  $attributes  User attributes override
     * @return User The created user with profile if provider
     */
    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        // Create profile for providers synchronously
        if ($role === 'provider') {
            $service = app(ProviderCreationService::class);
            $service->createProfileForUser($user);
        }

        return $user;
    }

    /**
     * Create a provider user with a profile.
     *
     * Convenience method for the most common case.
     *
     * @param  array  $attributes  User attributes override
     * @return User The created provider with profile
     */
    protected function createProvider(array $attributes = []): User
    {
        return $this->createUserWithRole('provider', $attributes);
    }

    /**
     * Create a regular user (non-provider).
     *
     * @param  array  $attributes  User attributes override
     * @return User The created user
     */
    protected function createUser(array $attributes = []): User
    {
        return $this->createUserWithRole('user', $attributes);
    }
}
