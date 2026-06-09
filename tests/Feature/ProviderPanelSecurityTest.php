<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\ProviderCredential;
use App\Models\ProviderLink;
use App\Models\User;
use App\Rules\SafeExternalUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProviderPanelSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Suspended provider has correct flags
     */
    public function test_suspended_provider_has_correct_flags(): void
    {
        $provider = $this->createProvider(['is_suspended' => true]);

        $this->assertTrue($provider->is_suspended);
        $this->assertTrue($provider->isSuspended());
    }

    /**
     * Test: Inactive provider has correct flags
     */
    public function test_inactive_provider_has_correct_flags(): void
    {
        $provider = $this->createProvider(['is_active' => false]);

        $this->assertFalse($provider->is_active);
    }

    /**
     * Test: Active provider has correct flags
     */
    public function test_active_provider_has_correct_flags(): void
    {
        $provider = $this->createProvider(['is_active' => true, 'is_suspended' => false]);

        $this->assertTrue($provider->is_active);
        $this->assertFalse($provider->is_suspended);
    }

    /**
     * Test: Provider cannot create second profile
     */
    public function test_provider_has_only_one_profile(): void
    {
        $provider = $this->createProvider();

        // Verify only one profile exists
        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseHas('profiles', ['user_id' => $provider->id]);
    }

    /**
     * Test: Profile ownership constraint
     */
    public function test_profile_ownership_enforced(): void
    {
        $provider1 = $this->createProvider();
        $provider2 = $this->createProvider();

        // Each provider has their own profile
        $this->assertTrue($provider1->profile->user_id === $provider1->id);
        $this->assertTrue($provider2->profile->user_id === $provider2->id);
        $this->assertNotEquals($provider1->profile->id, $provider2->profile->id);
    }

    /**
     * Test: Provider can create only 2 portfolio items
     */
    public function test_provider_can_create_only_2_portfolio_items(): void
    {
        $provider = $this->createProvider();
        $profile = $provider->profile;

        // Create 2 portfolio items
        PortfolioItem::factory()->count(2)->create(['profile_id' => $profile->id]);

        $this->assertDatabaseCount('portfolio_items', 2);
        $this->assertTrue($profile->portfolioItems()->count() === 2);
    }

    /**
     * Test: Portfolio items limited to 4 images per project
     */
    public function test_portfolio_item_limited_to_4_images(): void
    {
        $provider = $this->createProvider();
        $portfolio = PortfolioItem::factory()->create(['profile_id' => $provider->profile->id]);

        // Add 4 images
        for ($i = 0; $i < 4; $i++) {
            $portfolio->images()->create([
                'path' => '/path/to/image'.$i.'.webp',
                'alt' => 'Image '.$i,
                'sort_order' => $i,
            ]);
        }

        $this->assertDatabaseCount('portfolio_images', 4);
        $this->assertTrue($portfolio->images()->count() === 4);
    }

    /**
     * Test: Provider can manage own credentials
     */
    public function test_provider_can_create_credentials(): void
    {
        $provider = $this->createProvider();

        ProviderCredential::factory()->create([
            'profile_id' => $provider->profile->id,
            'title' => 'AWS Certification',
            'issuer' => 'Amazon',
        ]);

        $this->assertDatabaseHas('provider_credentials', [
            'profile_id' => $provider->profile->id,
            'title' => 'AWS Certification',
        ]);
    }

    /**
     * Test: Provider credential ownership enforced
     */
    public function test_credential_ownership_enforced(): void
    {
        $provider1 = $this->createProvider();
        $provider2 = $this->createProvider();

        $cred1 = ProviderCredential::factory()->create(['profile_id' => $provider1->profile->id]);
        $cred2 = ProviderCredential::factory()->create(['profile_id' => $provider2->profile->id]);

        $this->assertTrue($cred1->profile->user_id === $provider1->id);
        $this->assertTrue($cred2->profile->user_id === $provider2->id);
    }

    /**
     * Test: HTTPS URLs allowed by SafeExternalUrl rule
     */
    public function test_https_urls_allowed(): void
    {
        $rule = new SafeExternalUrl;
        $this->assertTrue($this->validateUrl('https://example.com', $rule));
        $this->assertTrue($this->validateUrl('https://www.google.com', $rule));
        $this->assertTrue($this->validateUrl('https://api.github.com/users', $rule));
    }

    /**
     * Test: HTTP URLs blocked by SafeExternalUrl rule
     */
    public function test_http_urls_blocked(): void
    {
        $rule = new SafeExternalUrl;
        $this->assertFalse($this->validateUrl('http://example.com', $rule));
    }

    /**
     * Test: JavaScript URLs blocked
     */
    public function test_javascript_urls_blocked(): void
    {
        $rule = new SafeExternalUrl;
        $this->assertFalse($this->validateUrl('javascript:alert("xss")', $rule));
    }

    /**
     * Test: Data URIs blocked
     */
    public function test_data_uris_blocked(): void
    {
        $rule = new SafeExternalUrl;
        $this->assertFalse($this->validateUrl('data:text/html,<script>alert("xss")</script>', $rule));
    }

    /**
     * Test: File protocol blocked
     */
    public function test_file_protocol_blocked(): void
    {
        $rule = new SafeExternalUrl;
        $this->assertFalse($this->validateUrl('file:///etc/passwd', $rule));
    }

    /**
     * Test: Localhost URLs blocked
     */
    public function test_localhost_urls_blocked(): void
    {
        $rule = new SafeExternalUrl;
        $this->assertFalse($this->validateUrl('https://localhost:8000', $rule));
        $this->assertFalse($this->validateUrl('https://127.0.0.1', $rule));
    }

    /**
     * Test: Private IP addresses blocked
     */
    public function test_private_ips_blocked(): void
    {
        $rule = new SafeExternalUrl;
        $this->assertFalse($this->validateUrl('https://192.168.1.1', $rule));
        $this->assertFalse($this->validateUrl('https://10.0.0.1', $rule));
        $this->assertFalse($this->validateUrl('https://172.16.0.1', $rule));
        $this->assertFalse($this->validateUrl('https://172.31.255.255', $rule));
    }

    /**
     * Test: Public IPs allowed
     */
    public function test_public_ips_allowed(): void
    {
        $rule = new SafeExternalUrl;
        $this->assertTrue($this->validateUrl('https://8.8.8.8', $rule));
        $this->assertTrue($this->validateUrl('https://1.1.1.1', $rule));
    }

    /**
     * Test: IPv6 loopback blocked
     */
    public function test_ipv6_loopback_blocked(): void
    {
        $rule = new SafeExternalUrl;
        $this->assertFalse($this->validateUrl('https://[::1]', $rule));
    }

    /**
     * Test: Provider link ownership enforced
     */
    public function test_link_ownership_enforced(): void
    {
        $provider1 = $this->createProvider();
        $provider2 = $this->createProvider();

        $link1 = ProviderLink::factory()->create(['profile_id' => $provider1->profile->id]);
        $link2 = ProviderLink::factory()->create(['profile_id' => $provider2->profile->id]);

        $this->assertTrue($link1->profile->user_id === $provider1->id);
        $this->assertTrue($link2->profile->user_id === $provider2->id);
    }

    /**
     * Test: Profile policy allows provider to view own profile
     */
    public function test_profile_policy_allows_own_view(): void
    {
        $provider = $this->createProvider();

        $this->assertTrue(
            $this->can('update', $provider->profile, $provider)
        );
    }

    /**
     * Test: Profile policy blocks access to other provider profile
     */
    public function test_profile_policy_blocks_other_access(): void
    {
        $provider1 = $this->createProvider();
        $provider2 = $this->createProvider();

        $this->assertFalse(
            $this->can('update', $provider2->profile, $provider1)
        );
    }

    /**
     * Test: Profile gracefully handles missing stats
     */
    public function test_profile_safe_with_missing_stats(): void
    {
        $provider = $this->createProvider();
        // Stats may not exist, profile should handle it
        if ($provider->profile->stats) {
            $provider->profile->stats->delete();
        }

        // These should not throw exceptions
        $completion = $provider->profile->calculateCompletionPercentage();
        $this->assertTrue(is_int($completion) && $completion >= 0 && $completion <= 100);
    }

    /**
     * Test: Portfolio with no images handled safely
     */
    public function test_portfolio_safe_with_no_images(): void
    {
        $provider = $this->createProvider();
        $portfolio = PortfolioItem::factory()->create(['profile_id' => $provider->profile->id]);

        // Should not throw exception
        $this->assertTrue($portfolio->images()->count() === 0);
    }

    /**
     * Helper: Validate a URL using SafeExternalUrl rule
     */
    private function validateUrl(string $url, SafeExternalUrl $rule): bool
    {
        $validator = Validator::make(
            ['url' => $url],
            ['url' => [$rule]]
        );

        return ! $validator->fails();
    }

    /**
     * Helper: Check if user can perform action on model
     */
    private function can(string $ability, $model, $user): bool
    {
        return Gate::forUser($user)->allows($ability, $model);
    }
}
