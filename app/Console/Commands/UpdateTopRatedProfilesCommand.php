<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ActivityLogService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

#[Signature('profiles:update-top-rated')]
#[Description('Precompute is_top_rated for admin display based on approved review counts and average rating.')]
class UpdateTopRatedProfilesCommand extends Command
{
    private const MIN_REVIEWS = 5;

    private const MIN_RATING = 4.5;

    public function handle(): int
    {
        $count = 0;

        DB::transaction(function () use (&$count): void {
            DB::table('profile_stats')->update(['is_top_rated' => false]);

            $qualifying = DB::table('reviews')
                ->select('profile_id')
                ->where('status', 'approved')
                ->whereNull('deleted_at')
                ->groupBy('profile_id')
                ->havingRaw('COUNT(*) >= ?', [self::MIN_REVIEWS])
                ->havingRaw('AVG(rating) >= ?', [self::MIN_RATING])
                ->pluck('profile_id');

            if ($qualifying->isNotEmpty()) {
                DB::table('profile_stats')
                    ->whereIn('profile_id', $qualifying)
                    ->update(['is_top_rated' => true]);
            }

            $count = $qualifying->count();
        });

        app(ActivityLogService::class)->logSystem(
            action: 'top_rated_profiles_updated',
            description: "Scheduler updated top-rated status: {$count} profile(s) qualify",
            properties: ['qualifying_count' => $count],
        );

        $this->info("Updated top-rated status. {$count} profile(s) qualify.");

        Cache::put('scheduler:top_rated:last_success_at', now()->toIso8601String(), now()->addDays(7));
        Cache::put('scheduler:top_rated:last_affected', $count, now()->addDays(7));

        return self::SUCCESS;
    }
}
