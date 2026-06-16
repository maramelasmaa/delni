# Queue Deployment Runbook

Delni uses Laravel's database queue for non-critical background work such as profile stats recalculation and user/profile cleanup. Production must run at least one queue worker whenever `QUEUE_CONNECTION=database`.

## Required Environment

```dotenv
QUEUE_CONNECTION=database
DB_QUEUE=default
QUEUE_FAILED_DRIVER=database-uuids
```

The `jobs`, `job_batches`, and `failed_jobs` tables must exist before the worker starts:

```bash
php artisan migrate --force
```

## Worker Command

Use this command for the default queue:

```bash
php artisan queue:work --queue=default --max-jobs=100 --max-time=3600 --tries=3
```

Restart workers after each deploy so code changes are picked up:

```bash
php artisan queue:restart
```

Check failures after deploy:

```bash
php artisan queue:failed
```

Retry all failed jobs only after confirming the underlying issue is fixed:

```bash
php artisan queue:retry all
```

## Supervisor Example

On a VPS, run the worker through Supervisor:

```ini
[program:delni-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/delni/artisan queue:work --queue=default --max-jobs=100 --max-time=3600 --tries=3
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

Reload Supervisor after creating or changing the file:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart delni-worker:*
```

## Cloud Platforms

For Laravel Cloud, Railway, or similar platforms, create a separate worker process/service using the same `php artisan queue:work` command. The web process alone is not enough.
