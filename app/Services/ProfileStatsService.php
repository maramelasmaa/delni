<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\ProfileStats;

class ProfileStatsService
{
    public function initializeForProfile(Profile $profile): void
    {
        ProfileStats::create([
            'profile_id' => $profile->id,
            'rating_avg' => 0.0,
            'reviews_count' => 0,
            'is_top_rated' => false,
            'is_homepage_featured' => false,
            'homepage_featured_until' => null,
            'is_top_search' => false,
            'top_search_until' => null,
            'is_top_category' => false,
            'top_category_until' => null,
            'is_top_subcategory' => false,
            'top_subcategory_until' => null,
        ]);
    }

    public function recalculate(Profile $profile): void
    {
        $stats = $profile->approvedReviews()
            ->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as avg_rating')
            ->first();

        $count = (int) ($stats->total ?? 0);
        $avg = round((float) ($stats->avg_rating ?? 0), 1);
        $topRated = $avg >= 4.5 && $count >= 5;

        $profile->stats()->updateOrCreate([], [
            'reviews_count' => $count,
            'rating_avg' => $avg,
            'is_top_rated' => $topRated,
        ]);
    }
}
