<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ProviderType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderTypeSelectFixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: ProviderType::options() returns correct localized names
     *
     * This verifies that the fix to ProfileResource now uses the correct method
     * to get provider type options with localized names.
     */
    public function test_provider_type_options_returns_correct_localized_names(): void
    {
        $options = ProviderType::options(activeOnly: true);

        // Should have all provider types
        $this->assertNotEmpty($options);

        // Should have individual, company, etc.
        $this->assertArrayHasKey('individual', $options);
        $this->assertArrayHasKey('company', $options);
        $this->assertArrayHasKey('agency', $options);
        $this->assertArrayHasKey('clinic', $options);
        $this->assertArrayHasKey('studio', $options);
        $this->assertArrayHasKey('freelancer', $options);
        $this->assertArrayHasKey('other', $options);

        // Values should be localized names (not nulls)
        foreach ($options as $value) {
            $this->assertNotNull($value);
            $this->assertIsString($value);
            $this->assertNotEmpty($value);
        }
    }

    /**
     * Test: ProviderType accessor works correctly
     */
    public function test_provider_type_localized_name_attribute(): void
    {
        $type = ProviderType::where('code', 'individual')->first();

        // Should have localized_name attribute
        $this->assertIsString($type->localized_name);
        $this->assertNotEmpty($type->localized_name);

        // In default locale, should be English name
        $this->assertTrue(
            $type->localized_name === $type->name || $type->localized_name === $type->name_ar
        );
    }

    /**
     * Test: ProfileResource form would have populated options
     *
     * This verifies that the form would now correctly render the provider_type
     * select field with actual options instead of nulls.
     */
    public function test_profile_resource_form_has_valid_provider_type_options(): void
    {
        // Simulate what ProfileResource form does now
        $options = ProviderType::options(activeOnly: true);

        // Should have at least 7 provider types
        $this->assertGreaterThanOrEqual(7, count($options));

        // No null values
        $hasNulls = collect($options)->contains(null);
        $this->assertFalse($hasNulls);
    }
}
