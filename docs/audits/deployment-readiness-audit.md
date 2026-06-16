# Delni Deployment Readiness Audit

Audit date: 2026-06-16  
Scope: PHP/Composer, PHP runtime config, Laravel env, cacheability, migrations, storage, images, Vite, queues, scheduler, OAuth, mail, security, and deploy commands.

## 1. Executive Summary

Verdict: **YES WITH WARNINGS - repository-level blockers found in the audit have been fixed, but production secrets and persistent storage still need deployment-time confirmation.**

The application is close. Local PHP extensions satisfy the installed Composer platform requirements, Laravel cache commands pass, the Vite build passes when network access to Bunny Fonts is available, database queue processing works, storage is linked, and core scheduler entries are registered.

Fixed in this pass:

- **PASS:** `composer.lock` is now in sync with `composer.json`; `composer validate` passes.
- **PASS:** The local sessions migration is recorded as run, and the migration is guarded for an existing `sessions` table.
- **PASS:** Tracked deployment docs no longer contain the credential-like values found during the audit.
- **PASS:** `.env.example` is now production-oriented and uses safe placeholders.
- **PASS:** `nixpacks.toml` no longer forces file sessions or runs database migrations during the build phase.
- **PASS:** `docs/deployment/queue.md` was added, and `php artisan queue:deployment-check` passes.
- **PASS:** npm audit now reports 0 vulnerabilities after adding a narrow `shell-quote` override.
- **PASS:** Docker image and local Compose stack were verified with Nginx, PHP-FPM, MySQL 8, queue worker, and scheduler.

Remaining deployment warnings:

- **WARNING:** Rotate any credentials that were previously committed or shared in deployment docs.
- **WARNING:** Confirm persistent upload storage before real production traffic; local disk is only safe on a durable VPS/volume.
- **WARNING:** The Vite build fetches Bunny Fonts and needs outbound HTTPS during build unless fonts are vendored.
- **WARNING:** For Coolify on Libya Spider, use the new Dockerfile path and mount persistent storage at `/var/www/html/storage/app/public`.

## 2. Deployment Verdict

**YES WITH WARNINGS - deploy only after production secrets are rotated/set and upload storage is persistent.**

Pre-deploy requirements:

1. Rotate any OAuth/API/admin credentials that appeared in previous docs.
2. Configure a persistent storage strategy: durable VPS disk/backup, cloud volume, or S3-compatible disk.
3. Set the production environment variables from `.env.example`.
4. Ensure build workers can reach Bunny Fonts, or vendor fonts before deploying in a restricted environment.
5. For Coolify/Libya Spider, follow `docs/deployment/COOLIFY_LIBYASPIDER_DEPLOYMENT.md`.

## 2.1 Docker / Coolify Verification

Status: **PASS**

Verified on 2026-06-16 with Docker Engine running:

- **PASS:** `docker build -t delni-docker-check .`
- **PASS:** `docker compose -f docker-compose.local.yml config`
- **PASS:** `docker compose -f docker-compose.local.yml up -d mysql app`
- **PASS:** `docker compose -f docker-compose.local.yml exec -T app php artisan migrate --force`
- **PASS:** `http://localhost:8088/up` returns `200`.
- **PASS:** `http://localhost:8088/` returns `200`.
- **PASS:** `docker compose -f docker-compose.local.yml exec -T app php artisan queue:deployment-check`
- **PASS:** `docker compose -f docker-compose.local.yml up -d worker scheduler`

Runtime services verified:

- App container starts Nginx and PHP-FPM through Supervisor.
- MySQL 8 service becomes healthy before the app starts.
- Queue worker container starts with the production queue command.
- Scheduler container runs `php artisan schedule:run --no-interaction` every minute.

Docker PHP modules verified in the app image include `bcmath`, `ctype`, `curl`, `dom`, `exif`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `PDO`, `pdo_mysql`, `xml`, `zip`, and `Zend OPcache`.

Local verification URL:

```text
http://localhost:8088
```

## 3. PHP / Composer Requirements

Status: **PASS**

Observed:

- Local PHP: `8.3.19`
- Laravel: `13.13.0`
- Filament: `5.6.6`
- Livewire: `4.3.1`
- Intervention Image: `4.1.2`
- Laravel Socialite: `5.27.0`
- Resend Laravel: `1.4.0`
- Spatie Permission: `8.0.0`

Explicit `composer.json` platform requirements:

- `php: ^8.3`
- `ext-intl`
- `ext-pdo_mysql`
- `ext-zip`

Composer command results:

- **PASS:** `composer check-platform-reqs`
- **PASS:** `composer validate`
- **PASS:** `composer install --dry-run --no-dev --optimize-autoloader`

Fix applied: `composer.lock` was refreshed and validated.

## 4. Missing Extensions

Status: **PASS locally, production must match**

Installed locally:

- `bcmath`
- `ctype`
- `curl`
- `dom`
- `exif`
- `fileinfo`
- `filter`
- `gd`
- `intl`
- `json`
- `mbstring`
- `openssl`
- `pdo`
- `pdo_mysql`
- `session`
- `tokenizer`
- `xml`
- `xmlreader`
- `xmlwriter`
- `zip`

Not installed locally:

- `imagick` - not required because `ProfileImageService` explicitly uses Intervention's GD driver.
- `Zend OPcache` - not visible in local CLI module output; required/recommended for production PHP-FPM performance.

Docker image note:

- `Zend OPcache` is enabled in the Docker app image used for Coolify/Libya Spider.

Production required extensions:

- Required by Composer/app: `ctype`, `dom`, `fileinfo`, `filter`, `hash`, `iconv`, `intl`, `json`, `libxml`, `mbstring`, `openssl`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `xmlreader`, `xmlwriter`, `zip`.
- Required by image handling: `gd`, `fileinfo`; keep `exif` enabled for safer image metadata/orientation handling even though current code does not directly call EXIF APIs.
- Recommended: `curl`, `bcmath`, `opcache`.

## 5. PHP Config Limits

Status: **PASS locally, production must set explicitly**

Local CLI values:

- `memory_limit=512M`
- `upload_max_filesize=2G`
- `post_max_size=2G`
- `max_execution_time=0`
- `max_input_vars=1000`
- `file_uploads=On`
- `date.timezone=UTC` for PHP CLI; Laravel app timezone is `Africa/Tripoli`.

Production recommendations:

- `memory_limit=512M`
- `upload_max_filesize=8M` or higher; current app validates avatar 2 MB and cover/portfolio 4 MB.
- `post_max_size` greater than `upload_max_filesize`, for example `16M`.
- `max_execution_time=60`
- `max_input_vars=3000`
- `file_uploads=On`
- `opcache.enable=1`
- PHP/Laravel timezone: use `Africa/Tripoli` consistently where possible.

## 6. Env Checklist

Status: **PASS with deployment-time secrets required**

`.env.example` is now production-oriented:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `DB_CONNECTION=mysql`
- `FILESYSTEM_DISK=public`
- `SESSION_SECURE_COOKIE=true`
- `CORS_ALLOWED_ORIGINS=https://delni.ly,https://www.delni.ly`
- `MAIL_MAILER=resend`
- Google OAuth variables are present with blank placeholders.
- `RESEND_KEY` is present with a blank placeholder.

Production env required:

```dotenv
APP_NAME=Delni
APP_ENV=production
APP_KEY=<generated secure key>
APP_DEBUG=false
APP_URL=https://delni.ly
APP_LOCALE=ar
APP_FALLBACK_LOCALE=ar
LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=<production host>
DB_PORT=3306
DB_DATABASE=<production database>
DB_USERNAME=<production user>
DB_PASSWORD=<secret>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

QUEUE_CONNECTION=database
CACHE_STORE=database
BROADCAST_CONNECTION=log

FILESYSTEM_DISK=public

MAIL_MAILER=resend
RESEND_KEY=<secret>
MAIL_FROM_ADDRESS=noreply@delni.ly
MAIL_FROM_NAME=Delni

GOOGLE_CLIENT_ID=<secret or console value>
GOOGLE_CLIENT_SECRET=<secret>
GOOGLE_REDIRECT_URL=https://delni.ly/auth/google/callback

CORS_ALLOWED_ORIGINS=https://delni.ly,https://www.delni.ly
TRUSTED_PROXIES=<proxy CIDR or * when appropriate for platform>
```

Important naming note: the code reads `GOOGLE_REDIRECT_URL`, not `GOOGLE_REDIRECT_URI`.

## 7. Database / Migration Readiness

Status: **PASS locally**

Command results:

- **PASS:** `php artisan migrate:status` reports `0001_01_01_000003_create_sessions_table` as run.
- **PASS:** `php artisan db:show` shows the local `delnidb.sessions` table exists.
- **PASS:** `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, `onboarding_tokens`, and core app tables exist locally.
- **PASS:** `php artisan migrate --pretend` shows the pending sessions migration would attempt to create `sessions`.

Risk:

The sessions migration now returns early when the `sessions` table already exists, allowing an existing-table database to record the migration safely.

Fix options:

- Fresh production database: run all migrations normally.
- Existing database: back up first, then run `php artisan migrate --force`; verify `migrate:status` afterward.

Seeder readiness:

- `DatabaseSeeder` runs only `RoleSeeder` and `AdminUserSeeder`.
- `MalamProviderSeeder` exists but is not called by default.
- Do not run demo/provider seeders in production unless intentionally approved.

## 8. Storage Readiness

Status: **WARNING for ephemeral platforms, PASS for configured VPS persistent disk**

Observed:

- `public/storage` is linked.
- `php artisan storage:link` reports the link already exists.
- `config/filesystems.php` has `public` disk at `storage/app/public`.
- Upload URLs use `Storage::disk('public')->url(...)`.
- Icon files use a private `icons` disk and are served through `/icon/{icon}`.

Risks:

- `ProfileImageService` always writes profile/portfolio images to the `public` disk.
- On Laravel Cloud/Railway-like ephemeral filesystems, uploaded images can disappear unless persistent storage is configured.
- `nixpacks.toml` no longer overrides `SESSION_DRIVER=file`.

Production requirements:

- VPS: ensure `storage/`, `bootstrap/cache/`, and `public/storage` permissions are owned by the web user and backed up.
- Cloud: configure persistent storage or switch uploads to S3-compatible storage before real production traffic.

## 9. Image Processing Requirements

Status: **PASS with GD**

Code path:

- `app/Services/ProfileImageService.php` uses `Intervention\Image\Drivers\Gd\Driver`.
- It validates MIME types, calls `getimagesize()`, resizes/crops images, and encodes WebP.

Required:

- `gd` with WebP support
- `fileinfo`
- `exif` recommended
- `memory_limit=512M` recommended

App upload validation:

- Avatar: 2 MB
- Cover image: 4 MB
- Portfolio image: 4 MB
- Accepted types: JPEG, PNG, WebP

## 10. Node / Vite Build Status

Status: **PASS with build-network warning**

Observed:

- Node: `v22.22.2`
- npm: `10.9.7`
- `npm ci`: passes.
- `npm run build`: passes with network access.

Important build behavior:

- The Laravel Vite plugin fetches Bunny Fonts during build via `laravel-vite-plugin/fonts`.
- In a network-restricted build environment, `npm run build` fails at `laravel:fonts` with `fetch failed`.

Security:

- `npm audit`: **PASS**, 0 vulnerabilities.

Fix:

- Ensure deployment build has outbound HTTPS access to Bunny Fonts, or vendor/localize fonts.

## 11. Queue Readiness

Status: **PASS**

Observed:

- `QUEUE_CONNECTION=database`
- `php artisan queue:failed`: no failed jobs found.
- `php artisan queue:work --once --tries=1 -vvv`: processed `App\Jobs\RecalculateProfileStatsJob` successfully.
- Jobs implementing queue contracts include `RecalculateProfileStatsJob` and `SoftDeleteUserProfileJob`.
- `Procfile` defines a worker process.

Project command:

- `php artisan queue:deployment-check` passes.
- Queue tables exist locally.

Production required:

```bash
php artisan queue:work --queue=default --max-jobs=100 --max-time=3600 --tries=3
php artisan queue:restart
php artisan queue:failed
```

Run the worker through Supervisor/systemd/VPS process manager or a separate cloud worker service.

## 12. Scheduler Readiness

Status: **PASS with infrastructure requirement**

`php artisan schedule:list` shows:

- Every minute: scheduler heartbeat.
- Daily: `subscriptions:expire`
- Daily: `placements:expire`
- Daily: `profiles:update-top-rated`
- Every five minutes: `users:clear-expired-locks`

Production required:

```bash
* * * * * cd /path/to/delni && php artisan schedule:run >> /dev/null 2>&1
```

On platforms without cron, configure a scheduler worker/process.

## 13. Google OAuth Readiness

Status: **PASS with deployment-time secrets required**

Observed:

- Routes exist: `/auth/google` and `/auth/google/callback`.
- Session regeneration happens after login.
- Inactive/suspended users are blocked.
- Provider and super admin roles are blocked from Google public login.
- Code reads `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URL`.

Production requirements:

- Google Console authorized redirect URI: `https://delni.ly/auth/google/callback`.
- `APP_URL=https://delni.ly`.
- `SESSION_SECURE_COOKIE=true`.
- Rotate any OAuth secret that appeared in committed documentation or history.

## 14. Resend / Mail Readiness

Status: **PASS with DNS/manual sending checks required**

Observed:

- `resend/resend-laravel` is installed.
- `config/services.php` supports `RESEND_KEY`.
- Current local `.env` uses SMTP settings for Resend, not `MAIL_MAILER=resend`.
- `.env.example` includes `RESEND_KEY`.

Production required:

- Either use first-party Resend mailer:

```dotenv
MAIL_MAILER=resend
RESEND_KEY=<secret>
MAIL_FROM_ADDRESS=noreply@delni.ly
MAIL_FROM_NAME=Delni
```

- Or explicitly document SMTP mode:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=resend
MAIL_PASSWORD=<resend api key>
```

Manual pre-launch checks:

- Verify `delni.ly` in Resend.
- Verify SPF/DKIM.
- Request password reset.
- Receive email.
- Reset password.
- Log in.

## 15. Security Findings

Status: **WARNING**

Findings:

- **PASS:** Tracked deployment docs were scrubbed of the credential-like values found during the audit.
- **PASS:** `.env.example` now sets `APP_DEBUG=false`.
- **PASS:** `.env.example` now restricts `CORS_ALLOWED_ORIGINS` to Delni domains.
- **WARNING:** Rotate any credentials that previously appeared in docs/history.
- **WARNING:** `routes/web.php` includes `/onboarding-test/{token}` only inside `app()->environment('local')`; this is acceptable if `APP_ENV=production` is set correctly.
- **WARNING:** `APP_KEY` exists locally; production needs its own generated key.
- **PASS:** No production debug packages such as Telescope/Debugbar were found in Composer direct dependencies.
- **PASS:** Route cache builds successfully.

Do not expose secrets in docs or examples. Use placeholders only.

## 16. Performance Blockers

Status: **WARNING**

No immediate performance blocker was proven during this audit.

Watch items:

- Admin resources and navigation badges may become slow as profiles/reviews/subscriptions grow.
- `activity_logs`, `profiles`, `reviews`, and marketplace ranking queries should be monitored after launch.
- Ensure `APP_DEBUG=false` so debug-only query collection is disabled.
- Use OPcache in production.
- Keep queue workers running so profile stats recalculation does not pile up.

## 17. Exact Deployment Commands

Coolify + Libya Spider recommended path:

- Build pack: Dockerfile
- Dockerfile path: `Dockerfile`
- Port: `8080`
- Persistent volume: `/var/www/html/storage/app/public`
- Post-deploy command:

```bash
php artisan migrate --force && php artisan storage:link && php artisan delni:setup-admin && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache && php artisan queue:restart
```

- Worker command:

```bash
php artisan queue:work --queue=default --max-jobs=100 --max-time=3600 --tries=3
```

- Scheduler command every minute:

```bash
php artisan schedule:run
```

Build phase:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Release phase:

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Post-deploy:

```bash
php artisan queue:restart
php artisan schedule:list
php artisan queue:deployment-check
php artisan queue:failed
```

One-time only:

```bash
php artisan key:generate
php artisan delni:setup-admin
```

VPS services:

```bash
php artisan queue:work --queue=default --max-jobs=100 --max-time=3600 --tries=3
php artisan schedule:run
```

## 18. Blockers / Warnings / Fixes

### PASS - Stale Composer Lock

Evidence: `composer validate` now passes.

Fix:

```bash
composer validate
```

### PASS - Migration History Inconsistent

Evidence: sessions migration is now recorded as run locally.

Fix:

- Fresh production: migrate normally.
- Existing production/staging: back up first, run migrations, then verify `migrate:status`.

### WARNING - Secrets Previously in Tracked Docs

Evidence: tracked docs were scrubbed, but any previously committed/shared value should be treated as exposed.

Fix:

- Rotate affected OAuth/API/admin secrets.
- Consider history cleanup if the repository was pushed publicly or shared.

### WARNING - Persistent Upload Storage

Evidence: app writes uploaded profile/portfolio images to local `public` disk.

Fix:

- VPS: persistent disk plus backups.
- Cloud: persistent volume or S3-compatible storage before real production.

### PASS - Queue Deployment Doc Missing

Evidence: `docs/deployment/queue.md` was added and `php artisan queue:deployment-check` passes.

Fix:

- Keep the queue runbook updated with the actual production process manager.

### PASS - Dev Dependency Vulnerability

Evidence: `npm audit` reports 0 vulnerabilities.

Fix:

```bash
npm ci
npm run build
```

### WARNING - Font Build Needs Network

Evidence: Vite build fails in a network-restricted environment when fetching Bunny Fonts; passes when outbound HTTPS is allowed.

Fix:

- Allow outbound HTTPS in build.
- Or vendor fonts and remove remote font fetch from build.

### WARNING - Env Examples Are Not Production Ready

Evidence: `.env.example` uses local values and lacks Google/Resend production keys.

Fix:

- Update `.env.example` with safe placeholders and production-oriented comments.

## Final Answers

Can Delni deploy safely?

**YES WITH WARNINGS - the repository-level blockers are fixed. Rotate exposed credentials and confirm persistent storage before production traffic.**

What PHP extensions are required?

`ctype`, `dom`, `fileinfo`, `filter`, `gd`, `hash`, `iconv`, `intl`, `json`, `libxml`, `mbstring`, `openssl`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `xmlreader`, `xmlwriter`, `zip`; recommended: `bcmath`, `curl`, `exif`, `opcache`.

What server settings are required?

PHP 8.3, OPcache enabled, `memory_limit=512M`, upload limits at least 4 MB plus request overhead, HTTPS, persistent storage for uploads, writable `storage` and `bootstrap/cache`, a queue worker, and a scheduler running every minute.

What Laravel Cloud/VPS env vars are required?

Set production `APP_*`, MySQL `DB_*`, secure database sessions, database queue/cache, production mail/Resend, Google OAuth, restricted CORS, trusted proxy, and a persistent storage strategy. Use placeholders in docs and real values only in the platform secret store.

What commands must run before/after deployment?

Before/release: `composer install --no-dev --optimize-autoloader`, `npm ci`, `npm run build`, `php artisan migrate --force`, `php artisan storage:link`, and Laravel cache commands. After: `php artisan queue:restart`, run/check queue worker, configure scheduler, verify mail/OAuth/uploads.
