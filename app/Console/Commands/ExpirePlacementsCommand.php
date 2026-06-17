<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ActivityLogService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

#[Signature('placements:expire')]
#[Description('Clear expired homepage featured placement flags')]
class ExpirePlacementsCommand extends Command
{
    private const PLACEMENTS = [
        ['flag' => 'is_homepage_featured', 'until' => 'homepage_featured_until'],
    ];

    public function handle(): int
    {
        $now = now()->toDateString();
        $affected = 0;

        foreach (self::PLACEMENTS as $placement) {
            $count = DB::table('profile_stats')
                ->where($placement['flag'], true)
                ->whereDate($placement['until'], '<', $now)
                ->update([
                    $placement['flag'] => false,
                    $placement['until'] => null,
                ]);

            $affected += $count;
        }

        if ($affected > 0) {
            app(ActivityLogService::class)->logSystem(
                action: 'placements_expired',
                description: "Scheduler expired {$affected} placement record(s)",
                properties: ['affected_count' => $affected],
            );
        }

        $this->info("Expired {$affected} placement records.");

        Cache::put('scheduler:placements_expire:last_success_at', now()->toIso8601String(), now()->addDays(7));
        Cache::put('scheduler:placements_expire:last_affected', $affected, now()->addDays(7));

        return self::SUCCESS;
    }
}
