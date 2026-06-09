<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderRootRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected User $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = User::create([
            'name' => 'Provider',
            'email' => 'provider@root.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $this->provider->assignRole('provider');

        Profile::create([
            'user_id' => $this->provider->id,
            'slug' => 'provider-root',
        ]);
    }

    public function test_provider_root_redirect_to_dashboard(): void
    {
        // Test what happens when authenticated provider accesses /provider root
        $response = $this->actingAs($this->provider)->get('/provider');

        // Should redirect (302)
        $this->assertEquals(302, $response->status());

        $target = $response->getTargetUrl();

        // Verify it redirects to dashboard, not to itself
        $this->assertStringContainsString('/provider/dashboard', $target);
        $this->assertNotEquals('http://localhost:8080/provider', $target, 'Redirect should not loop to /provider');
    }

    public function test_provider_root_redirect_chain(): void
    {
        // Test the actual redirect without following
        $response = $this->actingAs($this->provider)->get('/provider');

        // Should redirect somewhere (302)
        if ($response->status() === 302) {
            $target = $response->getTargetUrl();
            echo 'Redirect to: '.$target."\n";

            // Follow one redirect
            $followOne = $this->actingAs($this->provider)->get($target);

            // Second URL should not redirect back to /provider
            // (would indicate loop)
            if ($followOne->status() === 302) {
                $target2 = $followOne->getTargetUrl();
                echo 'Second redirect to: '.$target2."\n";

                // If it redirects back to /provider, that's the loop
                $this->assertNotEquals('/provider', $target2, 'Loop detected: /provider -> '.$target.' -> /provider');
            }
        }
    }
}
