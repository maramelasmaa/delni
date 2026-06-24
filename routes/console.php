<?php

use App\Console\Commands\ClearExpiredLocksCommand;
use App\Console\Commands\ExpirePlacementsCommand;
use App\Console\Commands\UpdateTopRatedProfilesCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn (): bool => Cache::put('scheduler:last_heartbeat_at', now()->toIso8601String(), now()->addDays(2)))
    ->name('scheduler-heartbeat')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command(ExpirePlacementsCommand::class)
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command(UpdateTopRatedProfilesCommand::class)
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command(ClearExpiredLocksCommand::class)
    ->everyFiveMinutes()
    ->withoutOverlapping(10)
    ->onOneServer();

Schedule::command('sanctum:prune-expired --hours=720')
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();
