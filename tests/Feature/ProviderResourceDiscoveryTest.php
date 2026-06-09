<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderResourceDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    protected User $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = User::create([
            'name' => 'Discovery Test',
            'email' => 'discovery@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $this->provider->assignRole('provider');

        Profile::create([
            'user_id' => $this->provider->id,
            'slug' => 'discovery-test',
        ]);
    }

    public function test_provider_panel_resources_are_registered(): void
    {
        $panel = Filament::getPanel('provider');

        $this->assertNotNull($panel, 'Provider panel should be registered');

        $resources = $panel->getResources();
        $resourceClasses = array_map(
            fn ($resource) => class_basename($resource),
            $resources
        );

        echo 'Resources: '.implode(', ', $resourceClasses)."\n";

        $this->assertNotEmpty($resources, 'Provider panel should have resources');
    }

    public function test_provider_can_access_profile_resource(): void
    {
        $panel = Filament::getPanel('provider');
        $resources = $panel->getResources();

        $profileResourceFound = false;
        foreach ($resources as $resource) {
            if (class_basename($resource) === 'ProfileResource') {
                $profileResourceFound = true;
                break;
            }
        }

        $this->assertTrue($profileResourceFound, 'ProfileResource should be registered in provider panel');
    }
}
