<?php

namespace App\Services\Chatbot;

/**
 * Builds structured prompts for DeepSeek API.
 *
 * Phase 1: Scaffolded but not used.
 * Phase 2: Will structure messages for natural Arabic responses.
 *
 * Key principle: Only send filtered, relevant data to API.
 * Never send entire database or all providers.
 */
class ChatPromptBuilderService
{
    /**
     * Build messages array for DeepSeek API.
     *
     * @param  string  $userMessage
     * @param  array<int, array<string, mixed>>  $providers Top 3-5 providers only
     * @param  array<string, mixed>  $context Search context (category, city, etc)
     * @return array<int, array<string, string>>
     */
    public function build(
        string $userMessage,
        array $providers = [],
        array $context = [],
    ): array {
        return [
            [
                'role' => 'system',
                'content' => $this->systemPrompt(),
            ],
            [
                'role' => 'user',
                'content' => $this->userPrompt($userMessage, $providers, $context),
            ],
        ];
    }

    /**
     * System prompt that defines assistant behavior.
     */
    private function systemPrompt(): string
    {
        return <<<'PROMPT'
أنت مساعد ذكي في تطبيق "دلني" - سوق خدمات ليبي.

مسؤولياتك:
- مساعدة المستخدمين في البحث عن مقدمي الخدمات بطريقة ودية وطبيعية
- الإجابة بالعربية فقط
- استخدام البيانات المتاحة فقط
- عدم اختراع معلومات

قيود صارمة:
- تكلم فقط عن مقدمي الخدمات المتاحة أدناه
- لا تخترع مقدمي خدمات جدد
- ركز على خدمات البحث فقط
- كن مختصراً (جملة واحدة إلى جملتين)
- لا تفصح عن بيانات داخلية أو معرفات قواعد البيانات

أسلوبك:
- ودود وملهم
- محترف ومفيد
- موجز وواضح
PROMPT;
    }

    /**
     * User message with context and providers.
     *
     * @param  array<int, array<string, mixed>>  $providers
     * @param  array<string, mixed>  $context
     */
    private function userPrompt(
        string $userMessage,
        array $providers = [],
        array $context = [],
    ): string {
        $providersList = empty($providers)
            ? 'لا توجد نتائج متاحة'
            : $this->formatProviders($providers);

        $category = $context['category'] ?? '—';
        $city = $context['city'] ?? '—';

        return <<<PROMPT
رسالة المستخدم: {$userMessage}

السياق:
- الفئة: {$category}
- المدينة: {$city}

مقدمو الخدمات المتاحة:
{$providersList}

الآن، قدم ردك الودود والمفيد:
PROMPT;
    }

    /**
     * Format providers for prompt (top 5 only, limited data).
     *
     * @param  array<int, array<string, mixed>>  $providers
     */
    private function formatProviders(array $providers): string
    {
        $providers = array_slice($providers, 0, 5);
        $formatted = [];

        foreach ($providers as $provider) {
            $name = $provider['business_name'] ?? '—';
            $category = $provider['category'] ?? '—';
            $rating = $provider['rating_avg'] ? "⭐ {$provider['rating_avg']}" : '—';
            $reviews = $provider['reviews_count'] ?? 0;

            $formatted[] = "• {$name} ({$category}) {$rating} ({$reviews} تقييم)";
        }

        return implode("\n", $formatted);
    }
}
