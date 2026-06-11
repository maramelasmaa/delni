<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Models\Profile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ChatContextBuilder
{
    /**
     * @param  array<string, mixed>  $intent
     * @param  Collection<int, Profile>  $providers
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    public function build(string $message, array $intent, Collection $providers, array $messages): array
    {
        return [
            'user_message' => mb_substr($message, 0, 500),
            'state' => array_intersect_key($intent['state'], array_flip([
                'service_query',
                'city_name',
                'provider_name_query',
                'min_experience_years',
                'pending_fields',
            ])),
            'messages' => array_slice($messages, -4),
            'providers' => $providers->take(5)->map(fn (Profile $profile): array => [
                'name' => $profile->business_name ?: $profile->user?->name,
                'city' => $profile->city?->name_ar ?: $profile->city?->name,
                'category' => $profile->category?->name_ar ?: $profile->category?->name,
                'rating' => (float) ($profile->stats?->rating_avg ?? 0),
                'reviews_count' => (int) ($profile->stats?->reviews_count ?? 0),
                'bio' => Str::limit((string) $profile->bio, 120),
            ])->values()->all(),
        ];
    }
}
