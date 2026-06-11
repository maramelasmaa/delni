<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Collection;

/**
 * Decides what the bot should do based on conversation state.
 *
 * Decision logic:
 * - greeting → reply greeting
 * - provider_join_question → show onboarding
 * - out_of_scope → polite decline
 * - provider_search missing service → ask service
 * - provider_search missing city → ask city
 * - provider_search complete → search providers
 */
class ChatDecisionService
{
    public function __construct(
        private ProviderSearchForChatService $searchService,
    ) {}

    /**
     * Make decision on what to do next.
     *
     * Returns decision with:
     * - action: 'ask_service', 'ask_city', 'search_providers', 'reply', 'error'
     * - message: message to send
     * - providers: results (if applicable)
     * - pending_field: field to set as pending (if asking)
     *
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function decide(
        string $intent,
        array $state,
        ?string $service = null,
        ?Collection $providers = null,
    ): array {
        return match ($intent) {
            'greeting' => $this->decideGreeting(),
            'provider_join_question' => $this->decideProviderJoin(),
            'out_of_scope' => $this->decideOutOfScope(),
            'provider_search' => $this->decideProviderSearch($state, $service, $providers),
            default => $this->decideProviderSearch($state, $service, $providers),
        };
    }

    /**
     * Greeting response.
     *
     * @return array<string, mixed>
     */
    private function decideGreeting(): array
    {
        return [
            'action' => 'reply',
            'message' => 'أهلاً وسهلاً بك في دلني! 👋

شنو نوع الخدمة اللي تبحث عنها؟',
            'pending_field' => null,
            'providers' => collect(),
        ];
    }

    /**
     * Provider onboarding response.
     *
     * @return array<string, mixed>
     */
    private function decideProviderJoin(): array
    {
        return [
            'action' => 'reply',
            'message' => 'مرحباً! 👨‍💼

يمكنك التسجيل كمزود خدمات من خلال تطبيق دلني:

1️⃣ اضغط على زر "التسجيل"
2️⃣ اختر "مزود خدمات"
3️⃣ أكمل البيانات المطلوبة

هل تريد معرفة المزيد عن المتطلبات؟',
            'pending_field' => null,
            'providers' => collect(),
        ];
    }

    /**
     * Out of scope response.
     *
     * @return array<string, mixed>
     */
    private function decideOutOfScope(): array
    {
        return [
            'action' => 'reply',
            'message' => 'أسف، لا أستطيع مساعدتك في هذا الموضوع.

دلني هو تطبيق للبحث عن مقدمي الخدمات. هل تريد البحث عن خدمة ما؟',
            'pending_field' => null,
            'providers' => collect(),
        ];
    }

    /**
     * Provider search decision logic.
     *
     * @param  array<string, mixed>  $state
     * @param  Collection<int, mixed>|null  $providers
     * @return array<string, mixed>
     */
    private function decideProviderSearch(
        array $state,
        ?string $service = null,
        ?Collection $providers = null,
    ): array {
        // Check for missing service
        if (empty($state['category_id']) && empty($state['service_query']) && empty($service)) {
            return [
                'action' => 'ask_service',
                'message' => 'شنو نوع الخدمة اللي تبحث عنها؟',
                'pending_field' => 'service',
                'providers' => collect(),
            ];
        }

        // Check for missing city
        if (empty($state['city_id'])) {
            $serviceQuery = $service ?? $state['service_query'] ?? 'الخدمة';

            return [
                'action' => 'ask_city',
                'message' => "في أي مدينة تبحث عن {$serviceQuery}؟",
                'pending_field' => 'city',
                'providers' => collect(),
            ];
        }

        // Have both service and city - search now
        if ($providers === null) {
            $providers = $this->searchService->search(
                cityId: $state['city_id'],
                categoryId: $state['category_id'] ?? null,
                subcategoryId: $state['subcategory_id'] ?? null,
                searchQuery: $state['service_query'] ?? null,
            );
        }

        if ($providers->isEmpty()) {
            return [
                'action' => 'no_results',
                'message' => 'ما لقيناش مطابق تماماً، لكن نقدر نعرضلك أقرب النتائج.',
                'pending_field' => null,
                'providers' => collect(),
            ];
        }

        return [
            'action' => 'search_results',
            'message' => $this->buildSearchMessage($state),
            'pending_field' => null,
            'providers' => $providers,
        ];
    }

    /**
     * Build search results message.
     *
     * @param  array<string, mixed>  $state
     */
    private function buildSearchMessage(array $state): string
    {
        $city = $state['city_name'] ?? 'المنطقة';
        $service = $state['service_query'] ?? $state['category_id'] ?? 'الخدمة';

        return "لقيتلك بعض مقدمي الخدمة المناسبين في {$city}:";
    }
}
