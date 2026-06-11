<?php

declare(strict_types=1);

namespace Tests\Feature\Chatbot;

use App\Models\Category;
use App\Models\City;
use App\Models\Profile;
use App\Models\ProfileStats;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotConversationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Config::set('deepseek.enabled', false);
        Http::preventStrayRequests();
    }

    public function test_service_without_city_asks_for_city(): void
    {
        Category::factory()->create(['name' => 'Lawyer', 'name_ar' => 'محامي']);

        $response = $this->postJson(route('api.chat.message'), [
            'session_id' => 'chat-test-1',
            'message' => 'نبي محامي',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('needs.city', true)
            ->assertJsonStructure(['message', 'providers', 'suggested_actions', 'needs', 'session_id']);
    }

    public function test_service_with_city_searches_immediately(): void
    {
        $city = City::factory()->create(['name' => 'Tripoli', 'name_ar' => 'طرابلس']);
        $category = Category::factory()->create(['name' => 'Lawyer', 'name_ar' => 'محامي']);
        $profile = $this->visibleProfile($city, $category, ['business_name' => 'مكتب خالد للمحاماة']);

        $response = $this->postJson(route('api.chat.message'), [
            'session_id' => 'chat-test-2',
            'message' => 'محامي طرابلس',
        ]);

        $response->assertOk()
            ->assertJsonPath('needs.city', false)
            ->assertJsonPath('providers.0.id', $profile->id)
            ->assertJsonPath('providers.0.name', 'مكتب خالد للمحاماة');
    }

    public function test_pending_city_answer_continues_previous_search(): void
    {
        $city = City::factory()->create(['name' => 'Benghazi', 'name_ar' => 'بنغازي']);
        $category = Category::factory()->create(['name' => 'AC', 'name_ar' => 'تكييف']);
        $profile = $this->visibleProfile($city, $category, ['business_name' => 'فني تكييف بنغازي']);

        $this->postJson(route('api.chat.message'), [
            'session_id' => 'chat-test-3',
            'message' => 'فني تكييف',
        ])->assertJsonPath('needs.city', true);

        $response = $this->postJson(route('api.chat.message'), [
            'session_id' => 'chat-test-3',
            'message' => 'benghazi',
        ]);

        $response->assertOk()
            ->assertJsonPath('providers.0.id', $profile->id);
    }

    public function test_provider_entity_name_searches_names(): void
    {
        $city = City::factory()->create(['name' => 'Tripoli', 'name_ar' => 'طرابلس']);
        $category = Category::factory()->create(['name' => 'Maintenance', 'name_ar' => 'صيانة']);
        $profile = $this->visibleProfile($city, $category, ['business_name' => 'فني زياد']);

        $response = $this->postJson(route('api.chat.message'), [
            'session_id' => 'chat-test-4',
            'message' => 'فني زياد',
        ]);

        $response->assertOk()
            ->assertJsonPath('providers.0.id', $profile->id);
    }

    public function test_hidden_profiles_are_never_returned(): void
    {
        $city = City::factory()->create(['name' => 'Tripoli', 'name_ar' => 'طرابلس']);
        $category = Category::factory()->create(['name' => 'Lawyer', 'name_ar' => 'محامي']);
        $this->visibleProfile($city, $category, ['business_name' => 'Visible Lawyer']);
        Profile::factory()
            ->for(User::factory()->create(['is_active' => false]))
            ->for($city)
            ->for($category)
            ->complete()
            ->create(['business_name' => 'Hidden Lawyer']);

        $response = $this->postJson(route('api.chat.message'), [
            'session_id' => 'chat-test-5',
            'message' => 'محامي طرابلس',
        ]);

        $response->assertOk();
        $this->assertNotContains('Hidden Lawyer', collect($response->json('providers'))->pluck('name')->all());
    }

    public function test_greeting_does_not_call_deepseek(): void
    {
        Config::set('deepseek.enabled', true);
        Config::set('deepseek.api_key', 'test-key');

        $response = $this->postJson(route('api.chat.message'), [
            'session_id' => 'chat-test-6',
            'message' => 'hello',
        ]);

        $response->assertOk()
            ->assertJsonPath('providers', []);
    }

    public function test_reset_clears_state(): void
    {
        Category::factory()->create(['name' => 'Lawyer', 'name_ar' => 'محامي']);
        City::factory()->create(['name' => 'Tripoli', 'name_ar' => 'طرابلس']);

        $this->postJson(route('api.chat.message'), [
            'session_id' => 'chat-test-7',
            'message' => 'نبي محامي',
        ])->assertJsonPath('needs.city', true);

        $this->postJson(route('api.chat.reset'), [
            'session_id' => 'chat-test-7',
        ])->assertOk();

        $this->postJson(route('api.chat.message'), [
            'session_id' => 'chat-test-7',
            'message' => 'طرابلس',
        ])->assertJsonPath('needs.service', true);
    }

    /**
     * @param  array<string, mixed>  $profileAttributes
     */
    private function visibleProfile(City $city, Category $category, array $profileAttributes = []): Profile
    {
        $user = User::factory()->create(['is_active' => true, 'is_suspended' => false]);
        $user->assignRole('provider');

        $profile = Profile::factory()
            ->for($user)
            ->for($city)
            ->for($category)
            ->complete()
            ->create(array_merge([
                'bio' => 'خدمة موثوقة من دلني',
                'experience_years' => 8,
            ], $profileAttributes));

        Subscription::factory()->for($user)->create([
            'is_active' => true,
            'ends_at' => now()->addMonth(),
        ]);

        ProfileStats::factory()->for($profile)->create([
            'rating_avg' => 4.8,
            'reviews_count' => 12,
        ]);

        return $profile;
    }
}
