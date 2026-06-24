<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'stats' => [
                'visible_providers_count' => (int) ($this['stats']['profiles_count'] ?? 0),
                'categories_count' => (int) ($this['stats']['categories_count'] ?? 0),
                'cities_count' => (int) ($this['stats']['cities_count'] ?? 0),
                'reviews_count' => (int) ($this['stats']['reviews_count'] ?? 0),
            ],
            'banners' => BannerResource::collection($this['banners']),
            'categories' => CategoryResource::collection($this['categories']->take(8)),
            'featured_providers' => ProviderCardResource::collection($this['featuredProviders']->take(8)),
            'suggested_providers' => ProviderCardResource::collection($this['suggestedProviders']->take(6)),
        ];
    }
}
