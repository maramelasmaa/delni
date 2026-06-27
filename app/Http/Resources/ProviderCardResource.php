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
            'logo_url' => $this->logo ? asset('storage/'.$this->logo) : null,
            'cover_url' => $this->cover_image ? asset('storage/'.$this->cover_image) : null,
            'is_featured' => $isFeatured,
            'is_favorited' => $isFavorited,
            'offers_remote_work' => (bool) $this->offers_remote_work,
            'whatsapp_url' => $this->whatsapp ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $this->whatsapp) : null,
            'phone' => $this->phone,
        ];
    }

}
