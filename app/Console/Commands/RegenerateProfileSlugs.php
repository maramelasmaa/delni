<?php

namespace App\Console\Commands;

use App\Models\Profile;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RegenerateProfileSlugs extends Command
{
    protected $signature = 'profiles:regenerate-slugs';

    protected $description = 'Regenerate profile slugs based on business names';

    public function handle(): int
    {
        $profiles = Profile::all();
        $updated = 0;

        foreach ($profiles as $profile) {
            $businessName = $profile->business_name ?? $profile->user?->name ?? 'Provider';
            $newSlug = Str::slug($businessName).'-'.Str::lower(Str::random(6));

            if ($newSlug !== $profile->slug) {
                $profile->update(['slug' => $newSlug]);
                $this->info("Updated: {$profile->id} → {$newSlug}");
                $updated++;
            }
        }

        $this->info("✓ Updated {$updated} profiles");

        return 0;
    }
}
