<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Provider\Resources\PortfolioResource;
use App\Filament\Provider\Resources\ProfileResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderResourceInstantiationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_resource_can_be_instantiated(): void
    {
        try {
            $resource = new ProfileResource;
            $this->assertNotNull($resource);
            echo "ProfileResource instantiated successfully\n";
        } catch (\Exception $e) {
            $this->fail('ProfileResource threw exception: '.$e->getMessage());
        }
    }

    public function test_portfolio_resource_can_be_instantiated(): void
    {
        try {
            $resource = new PortfolioResource;
            $this->assertNotNull($resource);
            echo "PortfolioResource instantiated successfully\n";
        } catch (\Exception $e) {
            $this->fail('PortfolioResource threw exception: '.$e->getMessage());
        }
    }

    public function test_provider_resources_exist_in_directory(): void
    {
        $resourcePath = app_path('Filament/Provider/Resources');
        $this->assertTrue(is_dir($resourcePath), 'Resources directory should exist');

        $files = glob($resourcePath.'/*.php');
        $this->assertGreaterThan(0, count($files), 'Should have resource files');

        foreach ($files as $file) {
            echo 'Found: '.basename($file)."\n";
        }
    }
}
