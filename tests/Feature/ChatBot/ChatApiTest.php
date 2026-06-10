<?php

namespace Tests\Feature\ChatBot;

use App\Models\Category;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    public function test_init_endpoint_returns_categories(): void
    {
        Category::factory()->create(['is_active' => true]);
        Category::factory()->create(['is_active' => true]);

        $response = $this->getJson(route('api.chat.init'));

        $response->assertStatus(200);
        $this->assertIsArray($response->json('categories'));
    }

    public function test_message_endpoint_validates_empty_message(): void
    {
        $response = $this->postJson(route('api.chat.message'), [
            'message' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_message_endpoint_rejects_long_messages(): void
    {
        $longMessage = str_repeat('a', 501);

        $response = $this->postJson(route('api.chat.message'), [
            'message' => $longMessage,
        ]);

        $response->assertStatus(422);
    }

    public function test_reset_endpoint_works(): void
    {
        $response = $this->postJson(route('api.chat.reset'), [
            'conversation_id' => 'test_conv_123',
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($response->json('new_conversation_id'));
    }

    public function test_conversation_id_generated(): void
    {
        $response = $this->postJson(route('api.chat.message'), [
            'message' => 'محامي',
            'conversation_id' => 'chat_' . uniqid(),
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($response->json('session_id'));
        $this->assertStringStartsWith('chat_', $response->json('session_id'));
    }

    public function test_api_response_includes_providers(): void
    {
        $response = $this->postJson(route('api.chat.message'), [
            'message' => 'test',
            'conversation_id' => 'test_conv_' . uniqid(),
        ]);

        $response->assertStatus(200);
        $this->assertIsArray($response->json('providers'));
        $this->assertGreaterThanOrEqual(0, count($response->json('providers')));
    }

    public function test_api_response_includes_message(): void
    {
        $response = $this->postJson(route('api.chat.message'), [
            'message' => 'محامي',
            'conversation_id' => 'test_conv_' . uniqid(),
        ]);

        $response->assertStatus(200);
        $this->assertIsString($response->json('message'));
    }

    public function test_message_response_has_intent(): void
    {
        $response = $this->postJson(route('api.chat.message'), [
            'message' => 'test search',
            'conversation_id' => 'test_conv_' . uniqid(),
        ]);

        $response->assertStatus(200);
        $this->assertIsString($response->json('intent'));
    }
}
