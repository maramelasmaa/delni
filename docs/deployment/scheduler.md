# Scheduler Operational Contract

Delni public visibility and marketplace ranking must remain correct even when the scheduler is delayed or stopped.

## Public Truth

Public provider visibility is computed at request time from:

- user is active
- user is not suspended
- profile is complete
- an active subscription exists with `subscriptions.is_active = true`
- subscription `ends_at` is today or later

Marketplace placement is active only when both are true:

- the placement flag is true
- the matching `*_until` date is today or later

Top-rated eligibility is computed from live approved, non-deleted reviews:

- at least 5 approved reviews
- average rating at least 4.5

The scheduler may clean up or precompute state, but it is not the source of truth for public visibility, placement, or top-rated ranking.

## Scheduled Tasks

Laravel scheduler should run every minute:

```cron
* * * * * cd /path/to/delni && php artisan schedule:run >> /dev/null 2>&1
```

Current scheduled tasks:

- `scheduler-heartbeat`: writes `scheduler:last_heartbeat_at` every minute.
- `subscriptions:expire`: cleanup only; deactivates expired subscription rows.
- `placements:expire`: cleanup only; clears expired placement flags.
- `profiles:update-top-rated`: precompute only; updates `profile_stats.is_top_rated` for admin/reporting views.
- `users:clear-expired-locks`: cleanup only; clears expired account locks.

Each scheduled task uses `withoutOverlapping()`. Each scheduled task also uses `onOneServer()`, so production must use a shared central cache driver such as `database`, `redis`, `memcached`, or `dynamodb`.

Do not use file cache for multi-server scheduling locks. File cache is local to one server and cannot coordinate `onOneServer()` locks across multiple application servers.

## Deploy Verification

Run these commands after deployment:

```bash
php artisan scheduler:health-check
php artisan subscriptions:audit
php artisan placements:audit
php artisan rankings:audit
```

Expected result:

- `scheduler:health-check` succeeds after the scheduler has written a fresh heartbeat.
- audit commands return no drift, or any intentional cleanup drift is repaired with the matching `--repair` command.

## Health Check

Run:

```bash
php artisan scheduler:health-check
```

This checks:

- scheduler heartbeat freshness
- last successful `subscriptions:expire`
- last successful `placements:expire`
- last successful `profiles:update-top-rated`

The command exits non-zero when heartbeat or daily cleanup/precompute runs are stale.

## Audit And Recovery

Use these commands to detect drift:

```bash
php artisan subscriptions:audit
php artisan placements:audit
php artisan rankings:audit
```

Use `--repair` to repair persisted cleanup/precompute state:

```bash
php artisan subscriptions:audit --repair
php artisan placements:audit --repair
php artisan rankings:audit --repair
```

These repairs do not define public truth. They only bring stored cleanup/precompute fields back in line with the rules that public queries already enforce.

## Deployment Guarantee

If every scheduler stops running:

- expired subscriptions remain hidden publicly because visibility checks `ends_at >= today`
- expired marketplace placements stop boosting because ranking checks `*_until >= today`
- stale `profile_stats.is_top_rated` cannot make a provider top-rated publicly because public ranking computes eligibility from approved reviews
- provider identity, profile, reviews, and portfolio remain persisted across expiry and renewal

Final verdict: Delni can remain visibility-safe and ranking-safe even if all schedulers stop running: YES.
