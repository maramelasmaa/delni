<?php

namespace Tests\Feature\ChatBot;

use App\Models\Category;
use App\Models\Subcategory;
use App\Services\Chatbot\CategoryResolverService;
use Tests\TestCase;

class CategoryResolverTest extends TestCase
{
    private CategoryResolverService $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(CategoryResolverService::class);
    }

    public function test_resolve_arabic_term_to_category(): void
    {
        $category = Category::factory()->create([
            'slug' => 'hvac-air-conditioning',
            'is_active' => true,
        ]);

        $result = $this->resolver->resolve('مكيف');

        $this->assertNotNull($result);
        $this->assertEquals($category->id, $result['category_id']);
        $this->assertEquals('high', $result['confidence']);
    }

    public function test_resolve_exact_category_name(): void
    {
        $category = Category::factory()->create([
            'name' => 'Plumbing Services',
            'is_active' => true,
        ]);

        $result = $this->resolver->resolve('Plumbing Services');

        $this->assertNotNull($result);
        $this->assertEquals($category->id, $result['category_id']);
    }

    public function test_resolve_arabic_category_name(): void
    {
        $category = Category::factory()->create([
            'name_ar' => 'خدمات السباكة',
            'is_active' => true,
        ]);

        $result = $this->resolver->resolve('خدمات السباكة');

        $this->assertNotNull($result);
        $this->assertEquals($category->id, $result['category_id']);
    }

    public function test_resolve_subcategory(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $subcategory = Subcategory::factory()
            ->create(['category_id' => $category->id, 'is_active' => true]);

        $result = $this->resolver->resolve($subcategory->name);

        $this->assertNotNull($result);
        $this->assertEquals($category->id, $result['category_id']);
        $this->assertEquals($subcategory->id, $result['subcategory_id']);
    }

    public function test_fuzzy_matching_for_typos(): void
    {
        $category = Category::factory()->create([
            'name' => 'Electrical Services',
            'is_active' => true,
        ]);

        // Typo should still match
        $result = $this->resolver->resolve('Electrcal Services');

        $this->assertNotNull($result);
        $this->assertEquals($category->id, $result['category_id']);
        $this->assertContains($result['confidence'], ['medium', 'high']);
    }

    public function test_return_null_for_unknown_term(): void
    {
        $result = $this->resolver->resolve('خدمة غير موجودة تماماً 1234567890');

        $this->assertNull($result);
    }

    public function test_return_null_for_empty_input(): void
    {
        $result = $this->resolver->resolve('');

        $this->assertNull($result);
    }

    public function test_get_all_categories_returns_active(): void
    {
        Category::factory()->create(['is_active' => true]);
        Category::factory()->create(['is_active' => true]);
        Category::factory()->create(['is_active' => false]);

        $categories = $this->resolver->getAllCategories();

        $this->assertGreaterThanOrEqual(2, count($categories));
        $this->assertTrue($categories->every(fn ($cat) => $cat->is_active));
    }

    public function test_get_subcategories_for_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Subcategory::factory(3)
            ->create(['category_id' => $category->id, 'is_active' => true]);

        $subcategories = $this->resolver->getSubcategoriesForCategory($category->id);

        $this->assertCount(3, $subcategories);
        $this->assertTrue($subcategories->every(fn ($sub) => $sub->category_id === $category->id));
    }

    public function test_case_insensitive_matching(): void
    {
        $category = Category::factory()->create([
            'name' => 'Plumbing Services',
            'is_active' => true,
        ]);

        $result1 = $this->resolver->resolve('plumbing services');
        $result2 = $this->resolver->resolve('PLUMBING SERVICES');

        $this->assertNotNull($result1);
        $this->assertNotNull($result2);
        $this->assertEquals($result1['category_id'], $result2['category_id']);
    }

    public function test_resolve_liyan_terms(): void
    {
        // Create actual category for common Libyan terms
        $plumbingCategory = Category::factory()->create([
            'slug' => 'plumbing-services',
            'is_active' => true,
        ]);

        $result = $this->resolver->resolve('سباك');

        $this->assertNotNull($result);
        $this->assertEquals($plumbingCategory->id, $result['category_id']);
    }
}
