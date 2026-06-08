<?php

namespace App\Observers;

use App\Models\Profile;
use App\Services\ActivityLogService;
use App\Services\ArabicNormalizationService;
use App\Services\ProfileCompletenessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

class ProfileObserver
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly ProfileCompletenessService $completeness,
        private readonly ArabicNormalizationService $normalization,
    ) {}

    public function created(Profile $profile): void
    {
        // Populate normalized columns for Arabic search
        $this->updateNormalizedColumns($profile);

        $this->activityLog->log(
            actorId: Context::get('actor_id') ?? Auth::id(),
            subject: $profile,
            action: 'profile_created',
            description: "Profile created for user #{$profile->user_id}",
            properties: [],
        );
    }

    public function updated(Profile $profile): void
    {
        // Update normalized columns if searchable content changed
        if ($profile->wasChanged(['business_name', 'bio'])) {
            $this->updateNormalizedColumns($profile);
        }

        if ($profile->wasChanged([
            'business_name',
            'bio',
            'city_id',
            'category_id',
            'whatsapp',
            'phone',
        ])) {
            $this->completeness->evaluate($profile);
        }

        $this->activityLog->log(
            actorId: Context::get('actor_id') ?? Auth::id(),
            subject: $profile,
            action: 'profile_updated',
            description: "Profile updated for user #{$profile->user_id}",
            properties: [
                'changed_fields' => array_keys($profile->getChanges()),
            ],
        );
    }

    private function updateNormalizedColumns(Profile $profile): void
    {
        // Update normalized columns for Arabic search indexing
        // These columns are used for LIKE queries to find providers
        // regardless of hamza variants, diacritics, etc.
        Profile::where('id', $profile->id)->update([
            'search_business_name' => $this->normalization->normalize($profile->business_name),
            'search_bio' => $this->normalization->normalize($profile->bio),
        ]);
    }

    public function deleted(Profile $profile): void
    {
        $this->activityLog->log(
            actorId: Context::get('actor_id') ?? Auth::id(),
            subject: $profile,
            action: 'profile_deleted',
            description: "Profile soft-deleted for user #{$profile->user_id}",
            properties: [],
        );
    }

    public function restored(Profile $profile): void
    {
        $this->updateNormalizedColumns($profile);

        $this->activityLog->log(
            actorId: Context::get('actor_id') ?? Auth::id(),
            subject: $profile,
            action: 'profile_restored',
            description: "Profile restored for user #{$profile->user_id}",
            properties: [],
        );
    }
}
