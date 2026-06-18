<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\ProfileSearchFilters;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\Subcategory;
use App\Services\ProfileSearchService;
use Tests\TestCase;

class ProfileSearchServiceTest extends TestCase
{
    private ProfileSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProfileSearchService::class);
    }

    private function visibleProfile(array $attributes = []): Profile
    {
        return Profile::factory()->complete()->withAccess()->withStats()->create($attributes);
    }

    public function test_keyword_matches_business_name(): void
    {
        $matched = $this->visibleProfile(['business_name' => 'شركة الامان للصيانة']);
        $other = $this->visibleProfile(['business_name' => 'مؤسسة البناء']);

        $ids = $this->service->search(new ProfileSearchFilters(keyword: 'الامان'))->pluck('id')->all();

        $this->assertContains($matched->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_keyword_matches_bio(): void
    {
        $matched = $this->visibleProfile(['bio' => 'متخصصون في تركيب الابواب']);
        $other = $this->visibleProfile(['bio' => 'خدمات تنظيف منازل']);

        $ids = $this->service->search(new ProfileSearchFilters(keyword: 'الابواب'))->pluck('id')->all();

        $this->assertContains($matched->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_keyword_matches_subcategory_arabic_name(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
            'name_ar' => 'سباكة',
            'is_active' => true,
        ]);

        $matched = $this->visibleProfile(['category_id' => $category->id]);
        $matched->subcategories()->attach($subcategory->id);
        $other = $this->visibleProfile(['category_id' => $category->id]);

        $ids = $this->service->search(new ProfileSearchFilters(keyword: 'سباكة'))->pluck('id')->all();

        $this->assertContains($matched->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_keyword_matches_subcategory_english_name(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id,
            'name' => 'plumbing',
            'is_active' => true,
        ]);

        $matched = $this->visibleProfile(['category_id' => $category->id]);
        $matched->subcategories()->attach($subcategory->id);
        $other = $this->visibleProfile(['category_id' => $category->id]);

        $ids = $this->service->search(new ProfileSearchFilters(keyword: 'plumbing'))->pluck('id')->all();

        $this->assertContains($matched->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_keyword_matches_category_name(): void
    {
        $category = Category::factory()->create(['name_ar' => 'خدمات منزلية', 'is_active' => true]);

        $matched = $this->visibleProfile(['category_id' => $category->id]);
        $other = $this->visibleProfile();

        $ids = $this->service->search(new ProfileSearchFilters(keyword: 'منزلية'))->pluck('id')->all();

        $this->assertContains($matched->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_keyword_matches_city_name(): void
    {
        $city = City::factory()->create(['name_ar' => 'طرابلس', 'is_active' => true]);

        $matched = $this->visibleProfile(['city_id' => $city->id]);
        $other = $this->visibleProfile();

        $ids = $this->service->search(new ProfileSearchFilters(keyword: 'طرابلس'))->pluck('id')->all();

        $this->assertContains($matched->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_city_filter(): void
    {
        $city = City::factory()->create(['is_active' => true]);
        $matched = $this->visibleProfile(['city_id' => $city->id]);
        $other = $this->visibleProfile();

        $ids = $this->service->search(new ProfileSearchFilters(cityId: $city->id))->pluck('id')->all();

        $this->assertContains($matched->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_category_filter(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $matched = $this->visibleProfile(['category_id' => $category->id]);
        $other = $this->visibleProfile();

        $ids = $this->service->search(new ProfileSearchFilters(categoryId: $category->id))->pluck('id')->all();

        $this->assertContains($matched->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_subcategory_id_filter(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $subcategory = Subcategory::factory()->create(['category_id' => $category->id, 'is_active' => true]);

        $matched = $this->visibleProfile(['category_id' => $category->id]);
        $matched->subcategories()->attach($subcategory->id);
        $other = $this->visibleProfile(['category_id' => $category->id]);

        $ids = $this->service->search(new ProfileSearchFilters(subcategoryId: $subcategory->id))->pluck('id')->all();

        $this->assertContains($matched->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_incomplete_profiles_excluded(): void
    {
        $hidden = Profile::factory()->withStats()->create([
            'is_complete' => false,
            'business_name' => 'شركة خفية',
        ]);

        $ids = $this->service->search(new ProfileSearchFilters(keyword: 'خفية'))->pluck('id')->all();

        $this->assertNotContains($hidden->id, $ids);
    }

    public function test_combined_keyword_and_city_filter(): void
    {
        $city = City::factory()->create(['is_active' => true]);
        $otherCity = City::factory()->create(['is_active' => true]);

        $matched = $this->visibleProfile(['city_id' => $city->id, 'business_name' => 'مقاول بناء']);
        $wrongCity = $this->visibleProfile(['city_id' => $otherCity->id, 'business_name' => 'مقاول بناء']);

        $ids = $this->service->search(new ProfileSearchFilters(cityId: $city->id, keyword: 'مقاول'))->pluck('id')->all();

        $this->assertContains($matched->id, $ids);
        $this->assertNotContains($wrongCity->id, $ids);
    }
}
