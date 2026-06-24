<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $ratingAverage = 0.0;
        $reviewsCount = 0;

        if ($this->relationLoaded('stats') && $this->stats) {
            $ratingAverage = (float) $this->stats->rating_avg;
            $reviewsCount = (int) $this->stats->reviews_count;
        } else {
            $ratingAverage = $this->approved_reviews_avg_rating !== null ? round((float) $this->approved_reviews_avg_rating, 1) : 0.0;
            $reviewsCount = (int) ($this->approved_reviews_count ?? 0);
        }
        $isFeatured = false;
        if ($this->relationLoaded('stats') && $this->stats) {
            $isFeatured = (bool) $this->stats->is_homepage_featured && ($this->stats->homepage_featured_until?->isFuture() ?? false);
        }

        $isFavorited = false;
        if (isset($this->isFavorited)) {
            $isFavorited = (bool) $this->isFavorited;
        } else {
            $currentUser = auth('sanctum')->user();
            if ($currentUser) {
                static $favoritesCache = null;
                if ($favoritesCache === null) {
                    $favoritesCache = $currentUser->favoriteProfiles()->pluck('profiles.id')->all();
                }
                $isFavorited = in_array($this->id, $favoritesCache);
            }
        }

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->business_name ?: ($this->user->name ?? ''),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'subcategories' => SubcategoryResource::collection($this->whenLoaded('subcategories')),
            'city' => new CityResource($this->whenLoaded('city')),
            'rating_average' => $ratingAverage,
            'reviews_count' => $reviewsCount,
            'logo_url' => $this->logo ? asset('storage/'.$this->logo) : $this->getFallbackLogo((int) $this->id),
            'cover_url' => $this->cover_image ? asset('storage/'.$this->cover_image) : $this->getFallbackCover((int) $this->id),
            'is_featured' => $isFeatured,
            'is_favorited' => $isFavorited,
            'whatsapp_url' => $this->whatsapp ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $this->whatsapp) : null,
            'phone' => $this->phone,
        ];
    }

    private function getFallbackLogo(int $providerId): string
    {
        $placeholders = [
            'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=250&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=250&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1618005198143-e528346d9a59?w=250&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1620641788421-7a1c342ea42e?w=250&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1614850523459-c2f4c699c52e?w=250&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1626785774625-ddc7c8241314?w=250&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1618005198140-5b1285223dc8?w=250&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1618005198130-fbf318ea1b09?w=250&auto=format&fit=crop&q=80',
        ];

        return $placeholders[abs($providerId) % count($placeholders)];
    }

    private function getFallbackCover(int $providerId): string
    {
        $placeholders = [
            'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1542744094-3a31f103e35f?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=600&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&auto=format&fit=crop&q=80',
        ];

        return $placeholders[abs($providerId) % count($placeholders)];
    }
}
