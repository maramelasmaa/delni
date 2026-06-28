<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

#[Signature('queue:deployment-check')]
#[Description('Verify queue deployment prerequisites for production workers.')]
class CheckQueueDeploymentCommand extends Command
{
    public function handle(): int
    {
        $failed = false;

        $this->info('Checking queue deployment readiness...');

        if (! $this->checkConnection()) {
            $failed = true;
        }

        if (! $this->checkTables()) {
            $failed = true;
        }

        if (! $this->checkDocumentation()) {
            $failed = true;
        }

        if ($failed) {
            $this->error('Queue deployment check failed.');

            return self::FAILURE;
        }

        $this->info('Queue deployment check passed.');

        return self::SUCCESS;
    }

    private function checkConnection(): bool
    {
        $connection = (string) config('queue.default');

        if (app()->isProduction() && $connection === 'sync') {
            $this->error('Production queue connection must not be sync. Set QUEUE_CONNECTION=database or another worker-backed driver.');

            return false;
        }

        if ($connection === 'sync') {
            $this->warn('QUEUE_CONNECTION is sync outside production. This is acceptable for tests/local work only.');

            return true;
        }

        $this->line("Queue connection: {$connection}");

        return true;
    }

    private function checkTables(): bool
    {
        $ok = true;
        $queueTable = (string) config('queue.connections.database.table', 'jobs');
        $batchTable = (string) config('queue.batching.table', 'job_batches');
        $failedTable = (string) config('queue.failed.table', 'failed_jobs');

        foreach ([$queueTable, $batchTable, $failedTable] as $table) {
            if (! Schema::hasTable($table)) {
                $this->error("Missing queue table: {$table}");
                $ok = false;

                continue;
            }

            $this->line("Queue table exists: {$table}");
        }

        return $ok;
    }

    /**
     * Advisory only: missing/incomplete docs are warned about but never fail the check.
     * This command doubles as the worker container healthcheck, so runtime health must
     * depend solely on the queue connection and tables — not on documentation contents.
     */
    private function checkDocumentation(): bool
    {
        $path = base_path('docs/deployment/queue.md');

        if (! File::exists($path)) {
            $this->warn('Queue deployment documentation not found: docs/deployment/queue.md');

            return true;
        }

        $contents = File::get($path);
        $required = [
            'php artisan queue:work',
            'php artisan queue:restart',
            'php artisan queue:failed',
            'php artisan queue:retry all',
            'worker',
        ];

        foreach ($required as $needle) {
            if (! str_contains($contents, $needle)) {
                $this->warn("Queue deployment documentation is missing: {$needle}");

                return true;
            }
        }

        $this->line('Queue deployment documentation is present.');

        return true;
    }
}
