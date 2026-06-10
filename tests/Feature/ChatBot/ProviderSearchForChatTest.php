<?php

namespace Tests\Feature\ChatBot;

use App\Data\ProviderChatResultDTO;
use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Chatbot\ProviderSearchForChatService;
use Carbon\Carbon;
use Tests\TestCase;

class ProviderSearchForChatTest extends TestCase
{
    private ProviderSearchForChatService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProviderSearchForChatService::class);
    }

    private function createVisibleProfile(): Profile
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);

        // Assign provider role (required for subscriptions)
        $user->assignRole('provider');

        $plan = SubscriptionPlan::factory()->create();

        $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::tomorrow(),
            'is_active' => true,
        ]);

        $category = Category::factory()->create(['is_active' => true]);
        $city = City::factory()->create(['is_active' => true]);

        $profile = Profile::factory()
            ->for($user)
            ->for($category)
            ->for($city)
            ->create(['is_complete' => true]);

        // Create ProfileStats for search visibility
        ProfileStats::factory()->for($profile)->create();

        return $profile;
    }

    public function test_visible_provider_is_returned(): void
    {
        $profile = $this->createVisibleProfile();

        $results = $this->service->search();

        $this->assertGreaterThan(0, count($results));
        $this->assertEquals($profile->id, $results->first()->id);
    }

    public function test_suspended_provider_is_hidden(): void
    {
        $user = User::factory()->create([
            'is_suspended' => true,
            'is_active' => true,
        ]);

        $plan = SubscriptionPlan::factory()->create();
        $user->assignRole('provider');
        $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::tomorrow(),
            'is_active' => true,
        ]);

        $category = Category::factory()->create(['is_active' => true]);
        $city = City::factory()->create(['is_active' => true]);

        $profile = Profile::factory()
            ->for($user)
            ->for($category)
            ->for($city)
            ->create(['is_complete' => true]);
        ProfileStats::factory()->for($profile)->create();

        $results = $this->service->search();

        $this->assertCount(0, $results);
    }

    public function test_inactive_user_profile_is_hidden(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
            'is_suspended' => false,
        ]);

        $plan = SubscriptionPlan::factory()->create();
        $user->assignRole('provider');
        $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::tomorrow(),
            'is_active' => true,
        ]);

        $category = Category::factory()->create(['is_active' => true]);
        $city = City::factory()->create(['is_active' => true]);

        $profile = Profile::factory()
            ->for($user)
            ->for($category)
            ->for($city)
            ->create(['is_complete' => true]);
        ProfileStats::factory()->for($profile)->create();

        $results = $this->service->search();

        $this->assertCount(0, $results);
    }

    public function test_incomplete_profile_is_hidden(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $plan = SubscriptionPlan::factory()->create();
        $user->assignRole('provider');
        $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::tomorrow(),
            'is_active' => true,
        ]);

        $category = Category::factory()->create(['is_active' => true]);
        $city = City::factory()->create(['is_active' => true]);

        Profile::factory()
            ->for($user)
            ->for($category)
            ->for($city)
            ->create(['is_complete' => false]);

        $results = $this->service->search();

        $this->assertCount(0, $results);
    }

    public function test_expired_subscription_profile_is_hidden(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_suspended' => false,
        ]);

        $plan = SubscriptionPlan::factory()->create();
        $user->assignRole('provider');
        $user->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => Carbon::yesterday(),
            'ends_at' => Carbon::yesterday()->addHour(),
            'is_active' => true,
        ]);

        $category = Category::factory()->create(['is_active' => true]);
        $city = City::factory()->create(['is_active' => true]);

        $profile = Profile::factory()
            ->for($user)
            ->for($category)
            ->for($city)
            ->create(['is_complete' => true]);
        ProfileStats::factory()->for($profile)->create();

        $results = $this->service->search();

        $this->assertCount(0, $results);
    }

    public function test_city_filter_works(): void
    {
        $city1 = City::factory()->create();
        $city2 = City::factory()->create();

        $user1 = User::factory()->create(['is_active' => true]);
        $user1->assignRole('provider');
        $plan = SubscriptionPlan::factory()->create();
        $user1->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::tomorrow(),
            'is_active' => true,
        ]);

        $category = Category::factory()->create();

        Profile::factory()
            ->for($user1)
            ->for($category)
            ->for($city1)
            ->create(['is_complete' => true]);

        $user2 = User::factory()->create(['is_active' => true]);
        $user2->assignRole('provider');
        $user2->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => Carbon::today(),
            'ends_at' => Carbon::tomorrow(),
            'is_active' => true,
        ]);

        Profile::factory()
            ->for($user2)
            ->for($category)
            ->for($city2)
            ->create(['is_complete' => true]);

        $results = $this->service->search(cityId: $city1->id);

        $this->assertCount(1, $results);
        $this->assertEquals($city1->id, $results->first()->id);
    }

    public function test_private_data_is_not_exposed(): void
    {
        $profile = $this->createVisibleProfile();

        $results = $this->service->search();
        $dto = $results->first();

        // Verify public data is available
        $this->assertNotNull($dto->businessName);
        $this->assertNotNull($dto->city);

        // Verify private data is not exposed
        $this->assertObjectNotHasProperty('email', $dto);
        $this->assertObjectNotHasProperty('password', $dto);
    }

    public function test_search_returns_dtos_not_models(): void
    {
        $this->createVisibleProfile();

        $results = $this->service->search();

        if ($results->count() > 0) {
            $this->assertInstanceOf(ProviderChatResultDTO::class, $results->first());
        }
    }
}
