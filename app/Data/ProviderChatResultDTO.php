<?php

namespace App\Data;

use App\Models\Profile;

/**
 * Safe data transfer object for chatbot provider results.
 *
 * Exposes ONLY public-safe provider data.
 * Never includes:
 * - Admin fields
 * - Internal notes
 * - Email addresses
 * - Suspension status
 * - Hidden/private data
 */
class ProviderChatResultDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $businessName,
        public readonly string $slug,
        public readonly string $city,
        public readonly string $category,
        public readonly ?string $subcategories = null,
        public readonly ?string $shortBio = null,
        public readonly ?float $ratingAvg = null,
        public readonly ?int $reviewsCount = null,
        public readonly ?string $logoUrl = null,
        public readonly bool $isFeatured = false,
        public readonly bool $isTopRated = false,
        public readonly ?string $whatsappNumber = null,
        public readonly ?string $phoneNumber = null,
        public readonly bool $offersRemoteWork = false,
    ) {}

    /**
     * Create DTO from a Profile model (with relations pre-loaded).
     */
    public static function from(Profile $profile): self
    {
        $shortBio = filled($profile->bio)
            ? substr($profile->bio, 0, 150)
            : null;

        $logoUrl = $profile->logo
            ? asset("storage/{$profile->logo}")
            : null;

        $subcategories = $profile->subcategories
            ->pluck('name_ar')
            ->join(', ');

        $approvedReviews = $profile->approvedReviews ?? [];
        $ratingAvg = $approvedReviews->isNotEmpty()
            ? round($approvedReviews->avg('rating'), 1)
            : null;

        return new self(
            id: $profile->id,
            businessName: $profile->business_name ?? $profile->user->name,
            slug: $profile->slug,
            city: $profile->city->localized_name,
            category: $profile->category->localized_name,
            subcategories: filled($subcategories) ? $subcategories : null,
            shortBio: $shortBio,
            ratingAvg: $ratingAvg,
            reviewsCount: $approvedReviews->count(),
            logoUrl: $logoUrl,
            isFeatured: $profile->stats?->is_featured && now()->isBefore($profile->stats?->featured_until),
            isTopRated: $ratingAvg !== null && $ratingAvg >= 4.5 && $approvedReviews->count() >= 5,
            whatsappNumber: $profile->whatsapp ?? null,
            phoneNumber: $profile->phone ?? null,
            offersRemoteWork: $profile->offers_remote_work ?? false,
        );
    }

    /**
     * Convert to array for JSON response.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_name' => $this->businessName,
            'slug' => $this->slug,
            'city' => $this->city,
            'category' => $this->category,
            'subcategories' => $this->subcategories,
            'bio' => $this->shortBio,
            'rating_avg' => $this->ratingAvg,
            'reviews_count' => $this->reviewsCount,
            'logo_url' => $this->logoUrl,
            'is_featured' => $this->isFeatured,
            'is_top_rated' => $this->isTopRated,
            'offers_remote_work' => $this->offersRemoteWork,
            'whatsapp' => $this->whatsappNumber,
            'phone' => $this->phoneNumber,
        ];
    }

    /**
     * Get profile URL for viewing full profile.
     */
    public function getProfileUrl(): string
    {
        return route('public.profile', ['provider' => $this->slug]);
    }
}
