<?php

namespace Tests\Feature\ChatBot;

use App\Services\Chatbot\IntentDetectionService;
use Tests\TestCase;

class IntentDetectionTest extends TestCase
{
    private IntentDetectionService $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = app(IntentDetectionService::class);
    }

    public function test_detect_greeting_intent(): void
    {
        $result = $this->detector->detect('أهلا');
        $this->assertEquals('greeting', $result['intent']);
        $this->assertEquals('high', $result['confidence']);
    }

    public function test_detect_multiple_greeting_variations(): void
    {
        $greetings = ['مرحبا', 'السلام عليكم', 'شلونك', 'كيفك'];

        foreach ($greetings as $greeting) {
            $result = $this->detector->detect($greeting);
            $this->assertEquals('greeting', $result['intent']);
        }
    }

    public function test_detect_join_question_intent(): void
    {
        $result = $this->detector->detect('كيف نسجل كمقدم خدمة؟');
        $this->assertEquals('provider_join_question', $result['intent']);
    }

    public function test_detect_support_question_intent(): void
    {
        $result = $this->detector->detect('شنو دلني؟');
        $this->assertEquals('support_question', $result['intent']);
    }

    public function test_detect_provider_search_intent(): void
    {
        $result = $this->detector->detect('محامي');
        $this->assertEquals('provider_search', $result['intent']);
    }

    public function test_detect_provider_search_for_unknown_term(): void
    {
        $result = $this->detector->detect('شيء عشوائي بدون معنى');
        $this->assertEquals('provider_search', $result['intent']);
    }

    public function test_detect_empty_message_defaults_to_greeting(): void
    {
        $result = $this->detector->detect('');
        $this->assertEquals('greeting', $result['intent']);
    }

    public function test_detect_whitespace_only_defaults_to_greeting(): void
    {
        $result = $this->detector->detect('   ');
        $this->assertEquals('greeting', $result['intent']);
    }

    public function test_all_results_have_required_fields(): void
    {
        $result = $this->detector->detect('أهلا');

        $this->assertArrayHasKey('intent', $result);
        $this->assertArrayHasKey('confidence', $result);
        $this->assertArrayHasKey('details', $result);
        $this->assertIsString($result['intent']);
        $this->assertIsString($result['confidence']);
        $this->assertIsArray($result['details']);
    }
}
