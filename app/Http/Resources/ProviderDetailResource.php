<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderDetailResource extends JsonResource
{
    // Pre-computed by ProviderController::show() to avoid N+1 queries inside toArray()
    public bool $isFavorited = false;

    public bool $canReview = false;

    public ?string $reviewStatusMessage = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 1. Rating average & Reviews count
        $ratingAverage = 0.0;
        $reviewsCount = 0;

        if ($this->relationLoaded('stats') && $this->stats) {
            $ratingAverage = (float) $this->stats->rating_avg;
            $reviewsCount = (int) $this->stats->reviews_count;
        } else {
            $ratingAverage = $this->approved_reviews_avg_rating !== null ? round((float) $this->approved_reviews_avg_rating, 1) : 0.0;
            $reviewsCount = (int) ($this->approved_reviews_count ?? 0);
        }

        $isFavorited = $this->isFavorited;
        $canReview = $this->canReview;
        $reviewStatusMessage = $this->reviewStatusMessage;

        // 4. Flat portfolio image list
        $portfolioImages = [];
        if ($this->relationLoaded('portfolioItems')) {
            foreach ($this->portfolioItems as $item) {
                if ($item->relationLoaded('images')) {
                    foreach ($item->images as $img) {
                        if ($img->path) {
                            $portfolioImages[] = asset('storage/'.$img->path);
                        }
                    }
                }
            }
        }

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->business_name ?: ($this->user->name ?? ''),
            'provider_type' => $this->provider_type,
            'description' => $this->bio,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'subcategories' => SubcategoryResource::collection($this->whenLoaded('subcategories')),
            'city' => new CityResource($this->whenLoaded('city')),
            'rating_average' => $ratingAverage,
            'reviews_count' => $reviewsCount,
            'logo_url' => $this->logo ? asset('storage/'.$this->logo) : null,
            'cover_url' => $this->cover_image ? asset('storage/'.$this->cover_image) : null,
            'portfolio_images' => $portfolioImages,
            'portfolio_items' => PortfolioItemResource::collection($this->whenLoaded('portfolioItems')),
            'phone' => $this->phone,
            'whatsapp_url' => $this->whatsapp ? 'https://wa.me/'.preg_replace('/[^0-9]/', '', $this->whatsapp) : null,
            'website' => $this->website,
            'social_links' => [
                'facebook' => $this->facebook,
                'instagram' => $this->instagram,
                'linkedin' => $this->linkedin,
                'github' => $this->github_username,
                'map_url' => $this->map_url,
            ],
            'offers_remote_work' => (bool) $this->offers_remote_work,
            'years_experience' => $this->experience_years,
            'is_favorited' => $isFavorited,
            'can_review' => $canReview,
            'review_status_message' => $reviewStatusMessage,
            'credentials' => ProviderCredentialResource::collection($this->whenLoaded('credentials')),
            'reviews' => ReviewResource::collection($this->whenLoaded('approvedReviews')),
        ];
    }

}
