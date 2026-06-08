# Queue Worker Deployment

Delni uses queued jobs for provider profile creation, profile cleanup, and profile/review statistics recalculation. Production must run a queue worker. Do not use `QUEUE_CONNECTION=sync` in production.

## Environment

Set the production queue driver:

```bash
QUEUE_CONNECTION=database
```

The database queue driver requires the queue migrations to be deployed. The app includes the `jobs`, `job_batches`, and `failed_jobs` tables in `database/migrations/0001_01_01_000002_create_jobs_table.php`.

## Worker Command

Run a long-lived worker on the server:

```bash
php artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=90
```

Use a process manager so the worker restarts if it exits.

## Supervisor Example

Example `/etc/supervisor/conf.d/delni-worker.conf`:

```ini
[program:delni-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/delni/artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/delni/storage/logs/worker.log
stopwaitsecs=3600
```

After adding or changing the Supervisor config:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start delni-worker:*
sudo supervisorctl status delni-worker:*
```

## Deployment Commands

Run migrations before starting workers:

```bash
php artisan migrate --force
```

Restart workers after every deploy so long-lived processes load the new code:

```bash
php artisan queue:restart
```

Supervisor should automatically start replacement workers after `queue:restart`.

## Failed Jobs

List failed jobs:

```bash
php artisan queue:failed
```

Retry all failed jobs after fixing the underlying issue:

```bash
php artisan queue:retry all
```

## Health Check

Verify deployment readiness:

```bash
php artisan queue:deployment-check
```

This command checks that production is not using the sync queue driver, the queue tables exist, the failed jobs table exists, and this deployment runbook contains the worker instructions.
