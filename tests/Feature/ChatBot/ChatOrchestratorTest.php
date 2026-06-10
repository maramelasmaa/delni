<?php

namespace Tests\Feature\ChatBot;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Chatbot\ChatOrchestratorService;
use Carbon\Carbon;
use Tests\TestCase;

class ChatOrchestratorTest extends TestCase
{
    private ChatOrchestratorService $orchestrator;

    private SubscriptionPlan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orchestrator = app(ChatOrchestratorService::class);
        $this->plan = SubscriptionPlan::factory()->create();
    }

    private function createVisibleProfile(
        ?Category $category = null,
        ?City $city = null,
    ): Profile {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);

        // Assign provider role (required for subscriptions)
        $user->assignRole('provider');

        $user->subscriptions()->create([
            'plan_id' => $this->plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::tomorrow(),
            'is_active' => true,
        ]);

        $category ??= Category::factory()->create();
        $city ??= City::factory()->create();

        $profile = Profile::factory()
            ->for($user)
            ->for($category)
            ->for($city)
            ->create(['is_complete' => true]);

        // Create ProfileStats for search visibility
        ProfileStats::factory()->for($profile)->create();

        return $profile;
    }

    public function test_greeting_intent_returns_greeting_message(): void
    {
        $response = $this->orchestrator->handle(
            message: 'أهلا',
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertEquals('greeting', $response['intent']);
        $this->assertStringContainsString('أهلاً', $response['message']);
        $this->assertIsArray($response['providers']);
        $this->assertCount(0, $response['providers']);
    }

    public function test_join_question_intent_returns_join_response(): void
    {
        $response = $this->orchestrator->handle(
            message: 'كيف نسجل كمقدم خدمة؟',
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertEquals('provider_join_question', $response['intent']);
        $this->assertStringContainsString('التسجيل', $response['message']);
    }

    public function test_support_question_intent_returns_support_response(): void
    {
        $response = $this->orchestrator->handle(
            message: 'شنو دلني؟',
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertEquals('support_question', $response['intent']);
        $this->assertStringContainsString('دلني', $response['message']);
    }

    public function test_provider_search_returns_results_when_category_and_city_provided(): void
    {
        $category = Category::factory()->create(['slug' => 'plumbing-services', 'is_active' => true]);
        $city = City::factory()->create(['is_active' => true]);

        $this->createVisibleProfile($category, $city);

        $response = $this->orchestrator->handle(
            message: 'سباك في '.$city->name,
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertEquals('provider_search', $response['intent']);
        $this->assertGreaterThan(0, count($response['providers']));
    }

    public function test_provider_search_asks_for_city_when_missing(): void
    {
        $category = Category::factory()->create(['slug' => 'plumbing-services']);
        City::factory()->create();

        $this->createVisibleProfile($category);

        $response = $this->orchestrator->handle(
            message: 'سباك',
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertEquals('provider_search', $response['intent']);
        $this->assertTrue($response['needs']['city']);
        $this->assertStringContainsString('مدينة', $response['message']);
    }

    public function test_provider_search_asks_for_category_when_missing(): void
    {
        $city = City::factory()->create();
        City::factory()->create();

        $response = $this->orchestrator->handle(
            message: 'خدمة في '.$city->name,
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertEquals('provider_search', $response['intent']);
        $this->assertTrue($response['needs']['category']);
        $this->assertStringContainsString('خدمة', mb_strtolower($response['message']));
    }

    public function test_provider_search_returns_fallback_when_no_results(): void
    {
        $category = Category::factory()->create(['slug' => 'plumbing-services']);
        $city = City::factory()->create();

        // Create a provider in a different city
        $otherCity = City::factory()->create();
        $this->createVisibleProfile($category, $otherCity);

        $response = $this->orchestrator->handle(
            message: 'سباك في '.$city->name,
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertEquals('provider_search', $response['intent']);
        $this->assertCount(0, $response['providers']);
        $this->assertStringContainsString('لم نجد', $response['message']);
    }

    public function test_hidden_providers_never_appear_in_response(): void
    {
        $category = Category::factory()->create(['slug' => 'plumbing-services']);
        $city = City::factory()->create();

        // Create visible provider
        $visibleProfile = $this->createVisibleProfile($category, $city);

        // Create suspended provider (should be hidden)
        $suspendedUser = User::factory()->create([
            'is_suspended' => true,
            'is_active' => true,
        ]);
        $suspendedUser->assignRole('provider');
        $suspendedUser->subscriptions()->create([
            'plan_id' => $this->plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::tomorrow(),
            'is_active' => true,
        ]);
        $suspendedProfile = Profile::factory()
            ->for($suspendedUser)
            ->for($category)
            ->for($city)
            ->create(['is_complete' => true]);
        ProfileStats::factory()->for($suspendedProfile)->create();

        $response = $this->orchestrator->handle(
            message: 'سباك في '.$city->name,
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertCount(1, $response['providers']);
        $this->assertEquals($visibleProfile->id, $response['providers'][0]['id']);
    }

    public function test_response_includes_provider_badges(): void
    {
        $category = Category::factory()->create(['slug' => 'plumbing-services']);
        $city = City::factory()->create();

        $profile = $this->createVisibleProfile($category, $city);
        $profile->update(['is_featured' => true]);

        $response = $this->orchestrator->handle(
            message: 'سباك في '.$city->name,
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertCount(1, $response['providers']);
        $this->assertIsArray($response['providers'][0]['badges']);
        $this->assertContains('مميز', $response['providers'][0]['badges']);
    }

    public function test_response_includes_suggested_actions_for_provider_search(): void
    {
        $category = Category::factory()->create(['slug' => 'plumbing-services']);
        $city = City::factory()->create();

        $this->createVisibleProfile($category, $city);

        $response = $this->orchestrator->handle(
            message: 'سباك في '.$city->name,
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertIsArray($response['suggested_actions']);
        $this->assertGreaterThan(0, count($response['suggested_actions']));
    }

    public function test_response_never_exceeds_max_providers(): void
    {
        $category = Category::factory()->create(['slug' => 'plumbing-services']);
        $city = City::factory()->create();

        // Create 10 providers
        for ($i = 0; $i < 10; $i++) {
            $this->createVisibleProfile($category, $city);
        }

        $response = $this->orchestrator->handle(
            message: 'سباك في '.$city->name,
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertLessThanOrEqual(5, count($response['providers']));
    }

    public function test_response_format_is_correct(): void
    {
        $response = $this->orchestrator->handle(
            message: 'أهلا',
            user: null,
            metadata: ['conversation_id' => 'test_123'],
        );

        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('intent', $response);
        $this->assertArrayHasKey('session_id', $response);
        $this->assertArrayHasKey('providers', $response);
        $this->assertArrayHasKey('suggested_actions', $response);
        $this->assertArrayHasKey('needs', $response);
        $this->assertIsArray($response['needs']);
        $this->assertArrayHasKey('city', $response['needs']);
        $this->assertArrayHasKey('category', $response['needs']);
    }
}
