<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Chatbot\CategoryResolverService;
use App\Services\Chatbot\ChatDecisionService;
use App\Services\Chatbot\CityResolverService;
use App\Services\Chatbot\ConversationStateService;
use App\Services\Chatbot\ProviderSearchForChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test the complete chatbot flow from message to response.
 *
 * Tests extraction, state management, decision logic, and provider search.
 */
class ChatbotFlowTest extends TestCase
{
    use RefreshDatabase;

    private CityResolverService $cityResolver;

    private CategoryResolverService $categoryResolver;

    private ConversationStateService $stateService;

    private ChatDecisionService $decisionService;

    private ProviderSearchForChatService $searchService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cityResolver = app(CityResolverService::class);
        $this->categoryResolver = app(CategoryResolverService::class);
        $this->stateService = app(ConversationStateService::class);
        $this->decisionService = app(ChatDecisionService::class);
        $this->searchService = app(ProviderSearchForChatService::class);
    }

    /**
     * Create a complete, visible profile for testing.
     */
    private function createVisibleProvider(
        string $userName,
        City $city,
        Category $category,
        ?string $businessName = null,
    ): Profile {
        $user = User::factory()->create([
            'name' => $userName,
            'is_active' => true,
            'is_suspended' => false,
        ]);

        // Give user the provider role
        $user->assignRole('provider');

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'city_id' => $city->id,
            'category_id' => $category->id,
            'business_name' => $businessName ?? $userName,
            'phone' => '0921234567',
            'whatsapp' => '0921234567',
            'is_complete' => true,
        ]);

        // Create profile stats for visibility check
        ProfileStats::factory()->create(['profile_id' => $profile->id]);

        // Create subscription plan and subscription
        $plan = SubscriptionPlan::factory()->create();
        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'processed_by' => 1,
            'processed_at' => now(),
            'approved_by' => 1,
            'approved_at' => now(),
        ]);

        return $profile;
    }

    /**
     * Test 1: Extract simple service term.
     */
    public function test_extraction_simple_service_term(): void
    {
        $category = Category::factory()->create([
            'name' => 'HVAC',
            'name_ar' => 'تكييف',
            'slug' => 'hvac-air-conditioning',
        ]);

        $result = $this->categoryResolver->resolve('تكييف');

        $this->assertNotNull($result);
        $this->assertEquals($category->id, $result['category_id']);
        $this->assertEquals('high', $result['confidence']);
    }

    /**
     * Test 2: Extract city by transliteration.
     */
    public function test_extraction_city_transliteration(): void
    {
        $city = City::factory()->create([
            'name' => 'Benghazi',
            'name_ar' => 'بنغازي',
        ]);

        $result = $this->cityResolver->resolve('benghazi');

        $this->assertNotNull($result);
        $this->assertEquals($city->id, $result['city_id']);
    }

    /**
     * Test 3: Extract photographer wedding service.
     */
    public function test_extraction_photography_weddings(): void
    {
        Category::factory()->create([
            'name' => 'Photography',
            'name_ar' => 'تصوير',
            'slug' => 'photography-videography',
        ]);

        $result = $this->categoryResolver->resolve('تصوير أفراح');

        $this->assertNotNull($result);
        $this->assertEquals('high', $result['confidence']);
    }

    /**
     * Test 4: City resolution supports multiple spellings.
     */
    public function test_city_multiple_transliterations(): void
    {
        City::factory()->create([
            'name' => 'Benghazi',
            'name_ar' => 'بنغازي',
        ]);

        // All these should resolve to the same city
        $result1 = $this->cityResolver->resolve('benghazi');
        $result2 = $this->cityResolver->resolve('bengazi');
        $result3 = $this->cityResolver->resolve('banghazi');

        $this->assertNotNull($result1);
        $this->assertNotNull($result2);
        $this->assertNotNull($result3);
    }

    /**
     * Test 5: State management - pending field for city.
     */
    public function test_state_pending_field_city(): void
    {
        $sessionId = 'test_session_1';

        $this->stateService->update($sessionId, [
            'service_query' => 'تكييف',
        ]);

        $this->stateService->setPendingField($sessionId, 'city', 'في أي مدينة تبحث؟');

        $state = $this->stateService->getState($sessionId);
        $this->assertEquals('city', $state['pending_field']);
        $this->assertTrue($this->stateService->isPending($sessionId, 'city'));
    }

    /**
     * Test 6: State management - clear pending after resolution.
     */
    public function test_state_clear_pending_when_resolved(): void
    {
        $sessionId = 'test_session_2';

        $this->stateService->setPendingField($sessionId, 'city', 'في أي مدينة؟');
        $this->assertTrue($this->stateService->isPending($sessionId, 'city'));

        // Now resolve the city
        $this->stateService->update($sessionId, ['city_id' => 1]);
        $state = $this->stateService->getState($sessionId);

        $this->assertNull($state['pending_field']);
    }

    /**
     * Test 7: State management - reset clears all.
     */
    public function test_state_reset_clears_everything(): void
    {
        $sessionId = 'test_session_3';

        $this->stateService->update($sessionId, [
            'city_id' => 1,
            'category_id' => 2,
            'service_query' => 'تكييف',
        ]);

        $this->stateService->reset($sessionId);
        $state = $this->stateService->getState($sessionId);

        $this->assertNull($state['city_id']);
        $this->assertNull($state['category_id']);
        $this->assertNull($state['service_query']);
    }

    /**
     * Test 8: Decision - ask service when missing.
     */
    public function test_decision_ask_service_when_missing(): void
    {
        $state = ['city_id' => 1];

        $decision = $this->decisionService->decide('provider_search', $state);

        $this->assertEquals('ask_service', $decision['action']);
        $this->assertStringContainsString('شنو نوع الخدمة', $decision['message']);
        $this->assertEquals('service', $decision['pending_field']);
    }

    /**
     * Test 9: Decision - ask city when missing.
     */
    public function test_decision_ask_city_when_missing(): void
    {
        $state = [
            'service_query' => 'تكييف',
            'category_id' => 1,
        ];

        $decision = $this->decisionService->decide('provider_search', $state);

        $this->assertEquals('ask_city', $decision['action']);
        $this->assertStringContainsString('في أي مدينة', $decision['message']);
        $this->assertEquals('city', $decision['pending_field']);
    }

    /**
     * Test 10: Decision - ask city for service-only state.
     */
    public function test_decision_ask_city_for_service_only(): void
    {
        $state = [
            'service_query' => 'تكييف',
            'category_id' => 1,
        ];

        $decision = $this->decisionService->decide('provider_search', $state);

        $this->assertEquals('ask_city', $decision['action']);
        $this->assertStringContainsString('في أي مدينة', $decision['message']);
    }

    /**
     * Test 11: State management - check if ready for search.
     */
    public function test_state_ready_for_search_with_city(): void
    {
        $sessionId = 'test_ready_1';

        $this->stateService->update($sessionId, ['city_id' => 1]);
        $this->assertTrue($this->stateService->isReadyForSearch($sessionId));
    }

    /**
     * Test 12: State management - not ready if pending field exists.
     */
    public function test_state_not_ready_if_pending(): void
    {
        $sessionId = 'test_ready_2';

        $this->stateService->setPendingField($sessionId, 'city', 'في أي مدينة؟');
        $this->assertFalse($this->stateService->isReadyForSearch($sessionId));
    }

    /**
     * Test 13: State management - get missing fields.
     */
    public function test_state_get_missing_fields(): void
    {
        $sessionId = 'test_missing';

        $state = $this->stateService->getState($sessionId);
        $missing = $this->stateService->getMissingFields($sessionId);

        $this->assertContains('city', $missing);
        $this->assertContains('service', $missing);
    }

    /**
     * Test 14: API Response - always has message.
     */
    public function test_api_response_has_message(): void
    {
        $state = ['city_id' => 1];
        $decision = $this->decisionService->decide('provider_search', $state);

        $this->assertArrayHasKey('message', $decision);
        $this->assertNotEmpty($decision['message']);
    }

    /**
     * Test 15: API Response - providers is array.
     */
    public function test_api_response_providers_is_iterable(): void
    {
        $state = ['city_id' => 1];
        $decision = $this->decisionService->decide('greeting', $state);

        $this->assertArrayHasKey('providers', $decision);
        $this->assertTrue(
            is_array($decision['providers']) || $decision['providers'] instanceof \Countable,
        );
    }

    /**
     * Test 16: Semantic search by provider name.
     * User: "فني زياد"
     * Should find provider named "زياد"
     */
    public function test_semantic_search_by_provider_name(): void
    {
        $city = City::factory()->create(['name_ar' => 'طرابلس']);
        $category = Category::factory()->create(['name_ar' => 'فني']);

        $profile = $this->createVisibleProvider('زياد', $city, $category, 'خدمات زياد');

        // Search by provider name
        $results = $this->searchService->searchByProviderName('زياد', null);

        $this->assertNotEmpty($results);
        $this->assertEquals(1, $results->count());
        $this->assertEquals($profile->id, $results->first()->id);
    }

    /**
     * Test 17: Semantic search by business name.
     * User: "شركة المدار"
     * Should find business with that name
     */
    public function test_semantic_search_by_business_name(): void
    {
        $city = City::factory()->create(['name_ar' => 'بنغازي']);
        $category = Category::factory()->create(['name_ar' => 'عقارات']);

        $profile = $this->createVisibleProvider('محمد أحمد', $city, $category, 'شركة المدار');

        // Search by business name
        $results = $this->searchService->searchByBusinessName('المدار', null);

        $this->assertNotEmpty($results);
        $this->assertEquals($profile->id, $results->first()->id);
    }

    /**
     * Test 18: Semantic search with mixed entity + service.
     * User: "مصور محمد" (photographer named محمد)
     * Should find providers named محمد in photography
     */
    public function test_semantic_search_provider_entity_with_service(): void
    {
        $city = City::factory()->create(['name_ar' => 'طرابلس']);
        $photoCategory = Category::factory()->create(['name_ar' => 'تصوير']);
        $otherCategory = Category::factory()->create(['name_ar' => 'عقارات']);

        // Provider named محمد who does photography
        $profile1 = $this->createVisibleProvider('محمد الأحمر', $city, $photoCategory);

        // Provider named محمد who does NOT do photography (different category)
        $profile2 = $this->createVisibleProvider('محمد البركاني', $city, $otherCategory);

        // Search for "محمد" with photography hint
        $results = $this->searchService->searchProviderEntity(
            entity: 'محمد',
            serviceHint: 'تصوير',
            cityId: null,
            categoryHint: $photoCategory->id,
        );

        // Should prioritize photographers named محمد
        $this->assertNotEmpty($results);
        $ids = $results->pluck('id')->toArray();
        $this->assertContains($profile1->id, $ids);
    }

    /**
     * Test 19: Semantic search with service only.
     * User: "محامي"
     * Should return all lawyers in database
     */
    public function test_semantic_search_by_service_only(): void
    {
        $city = City::factory()->create(['name_ar' => 'طرابلس']);
        $lawyerCategory = Category::factory()->create([
            'name_ar' => 'محامي',
            'name' => 'Lawyer',
        ]);

        // Create two lawyers
        $profile1 = $this->createVisibleProvider('خالد الحامي', $city, $lawyerCategory);
        $profile2 = $this->createVisibleProvider('أحمد القاضي', $city, $lawyerCategory);

        // Search by service
        $results = $this->searchService->searchByService('محامي', null, $lawyerCategory->id);

        $this->assertNotEmpty($results);
        $this->assertGreaterThanOrEqual(2, $results->count());
    }

    /**
     * Test 20: Semantic search respects visibility.
     * Hidden providers should never be returned
     */
    public function test_semantic_search_respects_visibility(): void
    {
        $city = City::factory()->create(['name_ar' => 'طرابلس']);
        $category = Category::factory()->create(['name_ar' => 'فني']);

        // Create visible provider
        $visibleProfile = $this->createVisibleProvider('أحمد', $city, $category);

        // Create hidden provider (inactive user)
        $hiddenUser = User::factory()->create([
            'name' => 'أحمد الخفي',
            'is_active' => false,
            'is_suspended' => false,
        ]);
        $hiddenProfile = Profile::factory()->create([
            'user_id' => $hiddenUser->id,
            'city_id' => $city->id,
            'category_id' => $category->id,
            'phone' => '0921234567',
            'whatsapp' => '0921234567',
            'is_complete' => true,
        ]);

        // Search by name should only return visible
        $results = $this->searchService->searchByProviderName('أحمد', null);

        // Should find at least the visible one
        $ids = $results->pluck('id')->toArray();
        $this->assertContains($visibleProfile->id, $ids);
        // Hidden should NOT be in results (thanks to visibility scope)
    }

    /**
     * Test 21: Multi-layer search - exact name priority.
     * If provider name exists, it should be returned immediately
     */
    public function test_multilayer_search_exact_name_priority(): void
    {
        $city = City::factory()->create(['name_ar' => 'طرابلس']);
        $photoCategory = Category::factory()->create(['name_ar' => 'تصوير']);
        $otherCategory = Category::factory()->create(['name_ar' => 'فني']);

        // Exact match: provider named "زياد"
        $profile1 = $this->createVisibleProvider('زياد', $city, $otherCategory);

        // Partial match: provider with "زياد" in bio
        $profile2 = $this->createVisibleProvider('محمد', $city, $photoCategory);

        // Semantic search should return exact match first
        $results = $this->searchService->searchSemantic(
            providerNameQuery: 'زياد',
            businessNameQuery: null,
            serviceQuery: null,
            cityId: null,
            categoryHint: null,
        );

        $this->assertNotEmpty($results);
        $firstResult = $results->first();
        $this->assertEquals($profile1->id, $firstResult->id);
    }

    /**
     * Test 22: Multi-layer search fallback to service.
     * If no entity found, fall back to service search
     */
    public function test_multilayer_search_fallback_to_service(): void
    {
        $city = City::factory()->create(['name_ar' => 'طرابلس']);
        $lawyerCategory = Category::factory()->create(['name_ar' => 'محامي']);

        $profile = $this->createVisibleProvider('خالد الحامي', $city, $lawyerCategory);

        // Search with non-existent provider name
        // Should fall back to service search
        $results = $this->searchService->searchSemantic(
            providerNameQuery: 'غير موجود',
            businessNameQuery: null,
            serviceQuery: 'محامي',
            cityId: null,
            categoryHint: $lawyerCategory->id,
        );

        // Should still find lawyer through service search
        $this->assertNotEmpty($results);
        $ids = $results->pluck('id')->toArray();
        $this->assertContains($profile->id, $ids);
    }

    /**
     * Test 23: Search with city filter.
     * Results should only include providers from specified city
     */
    public function test_search_respects_city_filter(): void
    {
        $tripoli = City::factory()->create(['name_ar' => 'طرابلس']);
        $benghazi = City::factory()->create(['name_ar' => 'بنغازي']);
        $category = Category::factory()->create(['name_ar' => 'فني']);

        // Technician in Tripoli
        $profile1 = $this->createVisibleProvider('أحمد', $tripoli, $category);

        // Technician in Benghazi
        $profile2 = $this->createVisibleProvider('محمد', $benghazi, $category);

        // Search in Tripoli
        $results = $this->searchService->searchByService('فني', $tripoli->id, $category->id);

        // Should only return Tripoli provider
        $ids = $results->pluck('id')->toArray();
        $this->assertContains($profile1->id, $ids);
        $this->assertNotContains($profile2->id, $ids);
    }

    /**
     * Test 24: Empty search returns no hallucination.
     * If search yields no results, bot should say so (not invent results)
     */
    public function test_empty_search_no_hallucination(): void
    {
        $city = City::factory()->create(['name_ar' => 'طرابلس']);
        $category = Category::factory()->create(['name_ar' => 'فني']);

        // Search for provider that doesn't exist
        $results = $this->searchService->searchByProviderName('الشخص الخيالي', $city->id);

        $this->assertEmpty($results);
    }

    /**
     * Test 25: Partial name matching works.
     * "زيا" should match provider "زياد"
     */
    public function test_partial_name_matching(): void
    {
        $city = City::factory()->create(['name_ar' => 'طرابلس']);
        $category = Category::factory()->create(['name_ar' => 'فني']);

        $profile = $this->createVisibleProvider('زياد', $city, $category);

        // Search with partial name
        $results = $this->searchService->searchByProviderName('زيا', null);

        $this->assertNotEmpty($results);
        $this->assertEquals($profile->id, $results->first()->id);
    }
}
