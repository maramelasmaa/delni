<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Models\Profile;
use Illuminate\Support\Collection;

class ChatResponseBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function welcome(string $sessionId, string $message = 'أهلاً، أنا مساعد دلني. قلّي شن الخدمة أو مقدم الخدمة اللي تبحث عليه؟'): array
    {
        return $this->shape($sessionId, $message, [], [
            'محامي في طرابلس',
            'فني تكييف في بنغازي',
            'مصور أفراح',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rateLimited(string $sessionId): array
    {
        return $this->shape($sessionId, 'وصلت للحد المسموح من الرسائل. حاول بعد شوية.');
    }

    /**
     * @return array<string, mixed>
     */
    public function safetyFallback(string $sessionId): array
    {
        return $this->shape($sessionId, 'نقدر نساعدك في البحث عن مقدمي الخدمات المتاحين علناً في دلني فقط.');
    }

    /**
     * @param  array<string, mixed>  $intent
     * @return array<string, mixed>
     */
    public function clarification(string $sessionId, array $intent): array
    {
        $state = $intent['state'];
        $message = $intent['needs']['city']
            ? 'أكيد. في أي مدينة تبحث عن '.($state['service_query'] ?: 'الخدمة').'؟'
            : 'شن نوع الخدمة أو اسم مقدم الخدمة اللي تبحث عليه؟';

        return $this->shape($sessionId, $message, [], [], $intent['needs']);
    }

    /**
     * @param  array<string, mixed>  $intent
     * @param  Collection<int, Profile>  $providers
     * @return array<string, mixed>
     */
    public function results(string $sessionId, array $intent, Collection $providers, ?string $aiMessage = null): array
    {
        $count = $providers->count();
        $message = $aiMessage ?: ($count > 0
            ? 'لقيتلك '.$count.' نتيجة مناسبة من دلني.'
            : 'ما لقيتش نتيجة مطابقة تماماً، لكن نقدر نوسّع البحث أو نجرب مدينة/خدمة قريبة.');

        $actions = [];
        if (filled($intent['state']['service_query'] ?? null)) {
            $actions[] = [
                'label' => 'عرض المزيد',
                'url' => route('public.search', ['q' => $intent['state']['service_query']]),
            ];
        }

        return $this->shape($sessionId, $message, $providers->map(fn (Profile $profile): array => [
            'id' => $profile->id,
            'name' => $profile->business_name ?: $profile->user?->name,
            'slug' => $profile->slug,
            'url' => route('public.provider', ['profile' => $profile->slug]),
            'logo' => $profile->logo ? asset('storage/'.$profile->logo) : null,
            'city' => $profile->city?->name_ar ?: $profile->city?->name,
            'category' => $profile->category?->name_ar ?: $profile->category?->name,
            'rating' => (float) ($profile->stats?->rating_avg ?? 0),
            'reviews_count' => (int) ($profile->stats?->reviews_count ?? 0),
            'whatsapp' => filled($profile->whatsapp) ? $profile->whatsapp : null,
        ])->values()->all(), $actions);
    }

    /**
     * @param  array<int, mixed>  $providers
     * @param  array<int, mixed>  $actions
     * @param  array<string, bool>  $needs
     * @return array<string, mixed>
     */
    private function shape(string $sessionId, string $message, array $providers = [], array $actions = [], array $needs = ['city' => false, 'service' => false]): array
    {
        return [
            'success' => true,
            'message' => $message,
            'providers' => $providers,
            'suggested_actions' => $actions,
            'needs' => [
                'city' => (bool) ($needs['city'] ?? false),
                'service' => (bool) ($needs['service'] ?? false),
            ],
            'session_id' => $sessionId,
        ];
    }
}
