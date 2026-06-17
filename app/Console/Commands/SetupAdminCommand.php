<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupAdminCommand extends Command
{
    protected $signature = 'delni:setup-admin {--force}';

    protected $description = 'Deprecated alias for delni:ensure-super-admin.';

    public function handle(): int
    {
        $this->warn('delni:setup-admin is deprecated. Using delni:ensure-super-admin with SUPER_ADMIN_* environment variables.');

        return $this->call('delni:ensure-super-admin', [
            '--force' => $this->option('force'),
        ]);
    }
}
