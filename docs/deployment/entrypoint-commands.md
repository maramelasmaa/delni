# Delni — Production Deployment Commands

## Architecture

Delni runs three processes in production, each from the same Docker image:

| Process | Command | Purpose |
|---------|---------|---------|
| `web` | supervisord → nginx + php-fpm | Serves HTTP requests |
| `worker` | `php artisan queue:work ...` | Processes background jobs |
| `scheduler` | `while true; do schedule:run; sleep 60; done` | Runs scheduled commands |

---

## 1. Build Phase (Dockerfile)

Runs once when building the Docker image.

```sh
# Frontend assets
npm ci
npm run build

# PHP dependencies (no dev, optimized autoloader)
composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-scripts

# Filament upgrade + package discovery
php artisan package:discover --ansi
php artisan filament:upgrade --ansi
```

**Note:** `npm run build` must be run before Docker build — the built `public/build/` folder is copied into the image. The CACHE_VERSION in `public/sw.js` must also be bumped before the build (see PWA section).

---

## 2. Container Startup (docker/entrypoint.sh)

Runs every time a container starts (web, worker, and scheduler services all run this entrypoint).

```sh
# Create required storage directories and link storage
mkdir -p storage/app/public storage/app/icons storage/framework/cache \
         storage/framework/sessions storage/framework/views storage/logs bootstrap/cache && \
php artisan storage:link --force

# Migrations (idempotent — safe on every restart)
php artisan migrate --force --no-interaction || true

# Seed roles (idempotent — uses firstOrCreate)
php artisan db:seed --class=RoleSeeder --force --no-interaction || true

# Ensure super admin exists (reads SUPER_ADMIN_* env vars)
php artisan delni:ensure-super-admin --no-interaction || true

# Seed demo data (idempotent — uses updateOrCreate/firstOrCreate)
php artisan db:seed --class=DemoSeeder --force --no-interaction || true

# Rebuild bootstrap caches
# NOTE: route:cache is excluded — web.php has closure routes that cannot be serialized
php artisan optimize:clear
php artisan config:cache
php artisan view:cache || true
php artisan event:cache || true

# Signal queue workers to reload (graceful restart)
php artisan queue:restart || true

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache public/storage || true
```

---

## 3. Procfile Release Phase (Railway Procfile / Nixpacks)

Used when deploying via Procfile (not Docker). Runs once before the web process starts.

```
release: php artisan migrate --force && \
         php artisan db:seed --class=RoleSeeder --force --no-interaction && \
         php artisan delni:ensure-super-admin --no-interaction && \
         php artisan optimize:clear && \
         php artisan config:cache && \
         php artisan view:cache && \
         php artisan event:cache
```

---

## 4. Process Commands

### Web
```sh
# Docker (supervisord → nginx + php-fpm)
/usr/bin/supervisord -c /etc/supervisord.conf

# Procfile / Railway
php artisan serve --host=0.0.0.0 --port=$PORT
```

### Queue Worker
```sh
php artisan queue:work \
  --queue=default \
  --tries=3 \
  --timeout=90 \
  --sleep=3 \
  --max-jobs=500 \
  --max-time=3600
```

- Must run as a **separate process**, not inside a deploy command
- Must be restarted after every deploy (`queue:restart` signals this gracefully)
- Handles: `RecalculateProfileStatsJob`, `SoftDeleteUserProfileJob`, mail (Resend), notifications

### Scheduler
```sh
sh -c "while true; do php artisan schedule:run --no-interaction; sleep 60; done"
```

Scheduled tasks:
| Task | Frequency |
|------|-----------|
| `ExpireSubscriptionsCommand` | Daily |
| `ExpirePlacementsCommand` | Daily |
| `UpdateTopRatedProfilesCommand` | Daily |
| `ClearExpiredLocksCommand` | Every 5 minutes |
| Scheduler heartbeat (cache key) | Every minute |

---

## 5. One-Time Setup Commands

Run **once** after first deployment, not on every deploy.

```sh
# Generate app key (only if APP_KEY is not set)
php artisan key:generate --force

# Create super admin (reads SUPER_ADMIN_EMAIL, SUPER_ADMIN_NAME, SUPER_ADMIN_PASSWORD from env)
php artisan delni:ensure-super-admin
```

---

## 6. PWA Cache Version (Before Every Build)

The service worker cache name must change on every deploy to invalidate old caches. Run this before `npm run build`:

```sh
# Linux / Docker build
HASH=$(git rev-parse --short HEAD)
sed -i "s/delni-public-[a-zA-Z0-9._-]*/delni-public-$HASH/" public/sw.js

# Windows (PowerShell)
$hash = git rev-parse --short HEAD
(Get-Content public/sw.js) -replace 'delni-public-[a-zA-Z0-9._-]*', "delni-public-$hash" | Set-Content public/sw.js
```

---

## 7. Database Tables

All required tables are covered by existing migrations — no generator commands needed in production.

| Table | Migration |
|-------|-----------|
| `users` | `0001_01_01_000000` |
| `cache` | `0001_01_01_000001` |
| `jobs`, `job_batches`, `failed_jobs` | `0001_01_01_000002` |
| `sessions` | `0001_01_01_000003` |
| `notifications` | `2026_06_03_182119` |
| `permissions`, `roles` | `2026_06_02_185551` |

---

## 8. Required Environment Variables

```
APP_KEY=                        # Required — generate once with key:generate
APP_ENV=production
APP_DEBUG=false
APP_URL=                        # Full URL (e.g. https://delni.railway.app)

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

TRUSTED_PROXIES=*               # Required behind Railway/Coolify reverse proxy

MAIL_MAILER=resend
RESEND_KEY=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URL=            # Must match actual domain, not localhost

SUPER_ADMIN_EMAIL=              # Read by delni:ensure-super-admin
SUPER_ADMIN_NAME=
SUPER_ADMIN_PASSWORD=
```

---

## 9. Commands NOT to Run in Production

| Command | Why |
|---------|-----|
| `php artisan key:generate` | Invalidates all sessions and encrypted data |
| `php artisan migrate:fresh` | Drops all tables |
| `php artisan migrate:reset` | Drops all tables |
| `php artisan db:seed` (without `--class`) | Runs DatabaseSeeder — safe here, but always prefer explicit class |
| `php artisan route:cache` | Fails — web.php has closure routes |
| `npm run dev` | Development server only |
| `php artisan serve` | Single-threaded development server — use nginx + php-fpm |
| `composer update` | Updates lockfile — only in development |
| `chmod -R 777 storage` | Insecure |
| `php artisan optimize:clear` alone | Clears all caches — immediately follow with config:cache etc. |

---

## 10. Filament

`php artisan filament:upgrade` runs during Docker build (in Dockerfile). No additional Filament commands are needed at deploy time.

`php artisan filament:assets` is not required — Filament assets are served from the vendor directory, which is copied into the image.

---

## Verdict

| Step | Commands | When |
|------|----------|------|
| Before build | Bump `CACHE_VERSION` in `sw.js` | Every deploy |
| Build | `npm run build` → Docker build | Every deploy |
| Container start | entrypoint.sh (migrate, seed, cache) | Every container restart |
| Web | supervisord (nginx + php-fpm) | Always running |
| Worker | `queue:work` | Always running, restart after deploy |
| Scheduler | `schedule:run` loop | Always running |
| One-time | `key:generate`, `ensure-super-admin` | First deploy only |
