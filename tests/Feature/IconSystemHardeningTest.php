<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\ProviderType;
use App\Services\IconSystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IconSystemHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_icon_saves_through_mass_assignment(): void
    {
        $city = City::create([
            'name' => 'Tripoli',
            'name_ar' => 'طرابلس',
            'slug' => 'tripoli',
            'icon' => 'heroicon-o-map-pin',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
            'icon' => 'heroicon-o-map-pin',
        ]);
    }

    public function test_category_icon_persists(): void
    {
        $category = Category::create([
            'name' => 'Design',
            'name_ar' => 'تصميم',
            'slug' => 'design',
            'icon' => 'heroicon-o-palette',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'icon' => 'heroicon-o-palette',
        ]);
    }

    public function test_city_icon_value_stored_and_retrievable(): void
    {
        $city = City::create([
            'name' => 'Tripoli',
            'name_ar' => 'طرابلس',
            'slug' => 'tripoli',
            'icon' => 'heroicon-o-map-pin',
            'is_active' => true,
        ]);

        $retrieved = City::find($city->id);
        $this->assertEquals('heroicon-o-map-pin', $retrieved->icon);
    }

    public function test_provider_type_icon_can_be_set(): void
    {
        $type = ProviderType::where('code', 'individual')->first();
        if (! $type) {
            $type = ProviderType::create([
                'code' => 'test-type-icon',
                'name' => 'Test Type',
                'name_ar' => 'نوع اختبار',
                'icon' => 'heroicon-o-user-circle',
                'sort_order' => 99,
                'is_active' => true,
            ]);
        } else {
            $type->update(['icon' => 'heroicon-o-user-circle']);
        }

        $retrieved = ProviderType::find($type->id);
        $this->assertEquals('heroicon-o-user-circle', $retrieved->icon);
    }

    public function test_category_icon_value_stored_and_retrievable(): void
    {
        $category = Category::create([
            'name' => 'Design',
            'name_ar' => 'تصميم',
            'slug' => 'design-test',
            'icon' => 'heroicon-o-palette',
            'is_active' => true,
        ]);

        $retrieved = Category::find($category->id);
        $this->assertEquals('heroicon-o-palette', $retrieved->icon);
    }

    public function test_icon_fillable_prevents_silent_failures(): void
    {
        $city = new City([
            'name' => 'Benghazi',
            'name_ar' => 'بنغازي',
            'slug' => 'benghazi',
            'icon' => 'heroicon-o-globe-alt',
            'is_active' => true,
        ]);

        $city->save();

        $this->assertEquals('heroicon-o-globe-alt', $city->fresh()->icon);
    }

    public function test_provider_type_fillable_works_with_update(): void
    {
        $type = ProviderType::where('code', 'individual')->firstOrCreate(
            ['code' => 'test-fillable'],
            ['name' => 'Test', 'name_ar' => 'اختبار', 'sort_order' => 99, 'is_active' => true]
        );

        $type->update(['icon' => 'heroicon-o-briefcase']);

        $this->assertEquals('heroicon-o-briefcase', $type->fresh()->icon);
    }

    public function test_category_fillable_works_with_fill(): void
    {
        $category = Category::create([
            'name' => 'Medical',
            'name_ar' => 'طبي',
            'slug' => 'medical',
            'is_active' => true,
        ]);

        $category->fill(['icon' => 'heroicon-o-heart'])->save();

        $this->assertEquals('heroicon-o-heart', $category->fresh()->icon);
    }

    public function test_icon_system_validates_heroicon_format(): void
    {
        $this->assertTrue(IconSystem::isValidHeroicon('heroicon-o-home'));
        $this->assertTrue(IconSystem::isValidHeroicon('heroicon-s-home'));
        $this->assertFalse(IconSystem::isValidHeroicon('invalid-icon'));
        $this->assertFalse(IconSystem::isValidHeroicon(''));
    }

    public function test_icon_system_contains_40_plus_icons(): void
    {
        $icons = IconSystem::getHeroiconsList();

        $this->assertGreaterThanOrEqual(40, count($icons));
    }

    public function test_seeded_icons_are_valid(): void
    {
        City::create([
            'name' => 'Tripoli',
            'name_ar' => 'طرابلس',
            'slug' => 'tripoli-test',
            'icon' => 'heroicon-o-map-pin',
            'is_active' => true,
        ]);

        $city = City::where('slug', 'tripoli-test')->first();

        $this->assertTrue(IconSystem::isValidHeroicon($city->icon));
    }

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();
    }
}
