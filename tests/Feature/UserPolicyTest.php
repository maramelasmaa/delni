<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_sole_super_admin_can_delete_regular_user(): void
    {
        $admin = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);
        $admin->assignRole('super_admin');

        $user = $this->createUser([
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $this->assertTrue($admin->can('delete', $user));
    }

    public function test_sole_super_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);
        $admin->assignRole('super_admin');

        $this->assertFalse($admin->can('delete', $admin));
    }
}
