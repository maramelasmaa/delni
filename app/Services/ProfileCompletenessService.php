<?php

namespace App\Services;

use App\Models\Profile;

class ProfileCompletenessService
{
    public function evaluate(Profile $profile): void
    {
        $isComplete = $this->meetsAllConditions($profile);

        if ($profile->is_complete !== $isComplete) {
            $profile->is_complete = $isComplete;
            $profile->saveQuietly();
        }
    }

    private function meetsAllConditions(Profile $profile): bool
    {
        $hasSubcategories = $profile->subcategories()->exists();

        return (
            filled($profile->business_name) ||
            filled($profile->user?->name)
        )
        && $profile->city_id !== null
        && $profile->category_id !== null
        && filled($profile->whatsapp)
        && filled($profile->phone)
        && $hasSubcategories;
    }
}
