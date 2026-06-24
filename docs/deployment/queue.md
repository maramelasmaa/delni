# Queue Worker Deployment

Required by `CheckQueueDeploymentCommand` (`php artisan queue:deployment-check`).

## Driver

`QUEUE_CONNECTION=database` — jobs stored in the `jobs` table (MySQL).

## Queues

| Queue | Jobs |
|---|---|
| `default` | `RecalculateProfileStatsJob`, `SoftDeleteUserProfileJob`, `ResetPasswordNotification` |

## Worker Command

```bash
php artisan queue:work database \
  --queue=default \
  --sleep=3 \
  --timeout=60 \
  --memory=256 \
  --max-jobs=1000 \
  --max-time=3600 \
  --tries=3
```

## In Docker

The `worker` compose service runs the command above. `CONTAINER_ROLE=worker` skips migrations in the entrypoint.

## In Coolify

Set the worker service command exactly to:

```bash
php artisan queue:work database --queue=default --sleep=3 --timeout=60 --memory=256 --max-jobs=1000 --max-time=3600 --tries=3
```

The worker service starts the worker. `queue:restart` only tells already-running workers to reload after deploy.

## Failed Jobs

Failed jobs are stored in `failed_jobs` table. Review via Filament admin or:

```bash
php artisan queue:failed
php artisan queue:retry all
```

## Restart After Deploy

```bash
php artisan queue:restart
```

Workers finish their current job then reload. Handled automatically by `entrypoint.sh` on `app` container start.

Verify the queue after restart:

```bash
php artisan queue:deployment-check
php artisan queue:failed
```
