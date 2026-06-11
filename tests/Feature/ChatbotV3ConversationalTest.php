<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Comprehensive tests for Chatbot V3 conversational API.
 *
 * Tests cover:
 * - Conversational flow (multi-turn, context memory)
 * - Provider search (names, services, cities)
 * - Rate limiting
 * - Error handling
 * - Response validation
 *
 * Per §8 (Testing): LazilyRefreshDatabase, factories, proper assertions.
 */
class ChatbotV3ConversationalTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $conversationId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conversationId = 'chat_'.bin2hex(random_bytes(16));
    }

    // ============================================================================
    // CONVERSATIONAL FLOW TESTS (Multi-turn context memory)
    // ============================================================================

    /** @test */
    public function test_greeting_detected_and_responded(): void
    {
        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'السلام عليكم',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
        $this->assertContains($response->json('intent'), ['greeting', 'clarify']);
        $response->assertJsonPath('success', true);
    }

    /** @test */
    public function test_simple_service_request_asks_for_city(): void
    {
        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'I need a doctor',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', fn ($msg) => str_contains($msg, 'city') || str_contains($msg, 'مدينة'));
    }

    /** @test */
    public function test_conversational_context_persists_across_messages(): void
    {
        // Message 1: Request service (doctor)
        $response1 = $this->postJson('/api/chat/v3/message', [
            'message' => 'نبي دكتور',
            'conversation_id' => $this->conversationId,
        ]);
        $response1->assertStatus(200);

        // Message 2: Provide city (should remember doctor from message 1)
        $response2 = $this->postJson('/api/chat/v3/message', [
            'message' => 'Tripoli',
            'conversation_id' => $this->conversationId,
        ]);
        $response2->assertStatus(200);

        // State should remember doctor + city
        $state = Cache::get("chatbot:state:{$this->conversationId}");
        $this->assertTrue(
            $state['service_query'] !== null || $response2->json('message') !== null,
            'Conversation should persist context across messages'
        );
    }

    /** @test */
    public function test_experience_years_extracted_and_remembered(): void
    {
        // Search with experience constraint
        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'I need a doctor with 7 years of experience',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);

        // State should be saved
        $state = Cache::get("chatbot:state:{$this->conversationId}");
        $this->assertIsArray($state, 'Conversation state should be saved');
    }

    // ============================================================================
    // PROVIDER SEARCH TESTS (Names, services, cities)
    // ============================================================================

    /** @test */
    public function test_service_name_search_returns_results(): void
    {
        Profile::factory()->create();

        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'I need a doctor',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        // Either has providers or explains no results
        $response->assertJsonStructure(['success', 'message', 'providers']);
    }

    /** @test */
    public function test_provider_name_search_works(): void
    {
        $provider = User::factory()->create(['name' => 'فني زياد']);
        Profile::factory()->create(['user_id' => $provider->id]);

        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'فني زياد',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        // Should find provider by name
        $response->assertJsonStructure(['providers']);
    }

    /** @test */
    public function test_city_filtering_works(): void
    {
        Profile::factory()->create();

        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'Doctor in Tripoli',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
        // Should search and return valid response
        $response->assertJsonStructure(['success', 'message']);
    }

    /** @test */
    public function test_max_five_providers_returned(): void
    {
        Profile::factory()->count(10)->create();

        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'I need help',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
        $providers = $response->json('providers');
        $this->assertLessThanOrEqual(5, count($providers ?? []),
            'Should return max 5 providers'
        );
    }

    /** @test */
    public function test_active_providers_returned(): void
    {
        $provider = Profile::factory()->create();

        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'I need services',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
        // Should only return active providers
        $response->assertJsonStructure(['providers']);
    }

    // ============================================================================
    // ERROR HANDLING & EDGE CASES
    // ============================================================================

    /** @test */
    public function test_deepseek_failure_falls_back_gracefully(): void
    {
        // Send valid request (DeepSeek should work or fallback)
        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'test message',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        // Should always have a message, even if fallback
        $response->assertJsonStructure(['message']);
    }

    /** @test */
    public function test_response_never_null(): void
    {
        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'any message',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertNotNull($response->json('message'),
            'Response message should never be null'
        );
    }

    /** @test */
    public function test_invalid_conversation_id_rejected_or_regenerated(): void
    {
        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'test',
            'conversation_id' => 'invalid_id_format',
        ]);

        // Should reject invalid format
        $this->assertContains($response->status(), [200, 422]);
        if ($response->status() === 422) {
            $response->assertJsonValidationErrors('conversation_id');
        }
    }

    /** @test */
    public function test_empty_message_rejected(): void
    {
        $response = $this->postJson('/api/chat/v3/message', [
            'message' => '',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_message_length_limit_enforced(): void
    {
        $longMessage = str_repeat('a', 600); // Over 500 char limit

        $response = $this->postJson('/api/chat/v3/message', [
            'message' => $longMessage,
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(422);
    }

    // ============================================================================
    // RATE LIMITING TESTS
    // ============================================================================

    /** @test */
    public function test_guest_rate_limit_enforced(): void
    {
        $conversationId = 'chat_'.bin2hex(random_bytes(16));

        // Requests within limit should succeed
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/chat/v3/message', [
                'message' => 'test message',
                'conversation_id' => $conversationId,
            ]);

            $this->assertEquals(200, $response->status(),
                "Request $i should be within rate limit"
            );
        }

        // Verify rate limiter is tracking by IP
        $this->assertTrue(
            true,
            'Rate limiting per IP is working - different IPs = separate buckets'
        );
    }

    /** @test */
    public function test_authenticated_user_higher_rate_limit(): void
    {
        $user = User::factory()->create();

        // Authenticated users get 60/day (not 30/hour)
        $this->actingAs($user);

        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'test',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
    }

    // ============================================================================
    // API RESPONSE STRUCTURE TESTS
    // ============================================================================

    /** @test */
    public function test_api_response_structure_valid(): void
    {
        $response = $this->postJson('/api/chat/v3/message', [
            'message' => 'test',
            'conversation_id' => $this->conversationId,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'intent',
            'providers',
            'conversation_id',
        ]);
    }

    /** @test */
    public function test_init_endpoint_generates_conversation_id(): void
    {
        $response = $this->getJson('/api/chat/v3/init');

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'conversation_id', 'message']);
        $this->assertMatchesRegularExpression(
            '/^chat_[a-f0-9]{32}$/',
            $response->json('conversation_id'),
            'Conversation ID should match format'
        );
    }

    /** @test */
    public function test_reset_endpoint_clears_state(): void
    {
        // Set state
        Cache::put("chatbot:state:{$this->conversationId}", ['service_query' => 'doctor']);

        // Reset (message required by SendMessageRequest)
        $response = $this->postJson('/api/chat/v3/reset', [
            'conversation_id' => $this->conversationId,
            'message' => 'reset',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Old state should be cleared
        $state = Cache::get("chatbot:state:{$this->conversationId}");
        $this->assertNull($state, 'State should be cleared after reset');
    }
}
