<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('subscriptions:expire')]
#[Description('Deactivate subscriptions whose end date has passed.')]
class ExpireSubscriptionsCommand extends Command
{
    public function handle(): int
    {
        $affected = 0;

        // Use Eloquent chunking so the observer fires per subscription (writing activity logs).
        Subscription::where('is_active', true)
            ->where('ends_at', '<', now())
            ->chunkById(100, function ($subscriptions) use (&$affected) {
                foreach ($subscriptions as $subscription) {
                    $subscription->update(['is_active' => false]);
                    $affected++;
                }
            });

        $this->info("Expired {$affected} subscription(s).");

        Cache::put('scheduler:subscriptions_expire:last_success_at', now()->toIso8601String(), now()->addDays(7));
        Cache::put('scheduler:subscriptions_expire:last_affected', $affected, now()->addDays(7));

        return self::SUCCESS;
    }
}
