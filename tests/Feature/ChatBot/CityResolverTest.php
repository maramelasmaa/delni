<?php

namespace Tests\Feature\ChatBot;

use App\Models\City;
use App\Services\Chatbot\CityResolverService;
use Tests\TestCase;

class CityResolverTest extends TestCase
{
    private CityResolverService $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(CityResolverService::class);
    }

    public function test_resolve_exact_city_name(): void
    {
        $city = City::factory()->create(['name' => 'Tripoli', 'is_active' => true]);

        $result = $this->resolver->resolve('Tripoli');

        $this->assertNotNull($result);
        $this->assertEquals($city->id, $result['city_id']);
        $this->assertEquals('high', $result['confidence']);
    }

    public function test_resolve_arabic_city_name(): void
    {
        $city = City::factory()->create(['name_ar' => 'طرابلس', 'is_active' => true]);

        $result = $this->resolver->resolve('طرابلس');

        $this->assertNotNull($result);
        $this->assertEquals($city->id, $result['city_id']);
        $this->assertEquals('high', $result['confidence']);
    }

    public function test_resolve_case_insensitive(): void
    {
        $city = City::factory()->create(['name' => 'Benghazi', 'is_active' => true]);

        $result1 = $this->resolver->resolve('benghazi');
        $result2 = $this->resolver->resolve('BENGHAZI');

        $this->assertNotNull($result1);
        $this->assertNotNull($result2);
        $this->assertEquals($city->id, $result1['city_id']);
        $this->assertEquals($city->id, $result2['city_id']);
    }

    public function test_return_null_for_unknown_city(): void
    {
        $result = $this->resolver->resolve('UnknownCityXYZ12345');

        $this->assertNull($result);
    }

    public function test_return_null_for_empty_input(): void
    {
        $result = $this->resolver->resolve('');

        $this->assertNull($result);
    }

    public function test_extract_city_from_message(): void
    {
        $city = City::factory()->create(['name' => 'Tripoli', 'is_active' => true]);

        $matches = $this->resolver->extractFromMessage('محامي في Tripoli');

        $this->assertGreaterThan(0, count($matches));
        $this->assertEquals($city->id, $matches[0]['city_id']);
    }

    public function test_extract_multiple_cities_from_message(): void
    {
        $city1 = City::factory()->create(['name' => 'Tripoli', 'is_active' => true]);
        $city2 = City::factory()->create(['name' => 'Benghazi', 'is_active' => true]);

        $matches = $this->resolver->extractFromMessage('من Tripoli أو Benghazi');

        $this->assertGreaterThanOrEqual(1, count($matches));
    }

    public function test_get_all_active_cities(): void
    {
        City::factory()->create(['is_active' => true]);
        City::factory()->create(['is_active' => true]);
        City::factory()->create(['is_active' => false]);

        $cities = $this->resolver->getAllCities();

        $this->assertGreaterThanOrEqual(2, count($cities));
        $this->assertTrue($cities->every(fn ($city) => $city->is_active));
    }

    public function test_fuzzy_matching_for_typos(): void
    {
        $city = City::factory()->create(['name' => 'Benghazi', 'is_active' => true]);

        // Minor typo should match
        $result = $this->resolver->resolve('Bengazi');

        $this->assertNotNull($result);
        $this->assertEquals($city->id, $result['city_id']);
    }

    public function test_result_includes_matched_name(): void
    {
        $city = City::factory()->create(['name' => 'Tripoli', 'is_active' => true]);

        $result = $this->resolver->resolve('Tripoli');

        $this->assertArrayHasKey('city_id', $result);
        $this->assertArrayHasKey('confidence', $result);
        $this->assertArrayHasKey('matched_name', $result);
        $this->assertNotNull($result['matched_name']);
    }
}
