# Coolify + Libya Spider Deployment Guide

Use this guide when Libya Spider provides the VPS/server and Coolify manages the deployment.

## Recommended Topology

- **Server:** Libya Spider VPS with Ubuntu 22.04/24.04.
- **Control panel:** Coolify installed on the VPS.
- **Application:** Delni Dockerfile build from this repository.
- **Database:** MySQL 8 managed by Coolify, or an external Libya Spider/MySQL service.
- **Queue:** separate Coolify service using the same image.
- **Scheduler:** separate Coolify scheduled job or service that runs Laravel's scheduler every minute.
- **Uploads:** persistent Coolify volume mounted to `storage/app/public`.

## Server Requirements

Minimum for MVP:

- 2 vCPU
- 2 GB RAM minimum, 4 GB safer
- 30 GB SSD minimum
- Ubuntu 22.04/24.04
- Ports `80` and `443` open
- SSH access

Recommended DNS:

- `delni.ly` -> VPS public IP
- `www.delni.ly` -> VPS public IP

## Coolify Project Setup

1. Create a Coolify project, for example `delni-production`.
2. Add a new resource from Git.
3. Select this repository.
4. Build pack: **Dockerfile**.
5. Dockerfile path: `Dockerfile`.
6. Exposed port: `8080`.
7. Domain: `https://delni.ly`.
8. Enable HTTPS/Let's Encrypt in Coolify.

## Local Docker Rehearsal

Use this before deploying to Coolify when Docker Engine is available locally:

```bash
php artisan key:generate --show
APP_KEY="base64:your-generated-key" docker compose -f docker-compose.local.yml config
APP_KEY="base64:your-generated-key" docker compose -f docker-compose.local.yml up -d mysql app
APP_KEY="base64:your-generated-key" docker compose -f docker-compose.local.yml exec -T app php artisan migrate --force
APP_KEY="base64:your-generated-key" docker compose -f docker-compose.local.yml up -d worker scheduler
```

PowerShell equivalent:

```powershell
$env:APP_KEY = "base64:your-generated-key"
docker compose -f docker-compose.local.yml config
docker compose -f docker-compose.local.yml up -d mysql app
docker compose -f docker-compose.local.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.local.yml up -d worker scheduler
```

Then verify:

```bash
curl http://localhost:8088/up
curl http://localhost:8088/
APP_KEY="base64:your-generated-key" docker compose -f docker-compose.local.yml exec -T app php artisan queue:deployment-check
APP_KEY="base64:your-generated-key" docker compose -f docker-compose.local.yml ps
```

Expected result: MySQL is healthy, the app responds on port `8088`, Nginx and PHP-FPM stay running, and worker/scheduler services are up.

## Persistent Storage

Create a persistent storage volume in Coolify:

```text
Mount path: /var/www/html/storage/app/public
```

This is required for:

- profile avatars
- cover images
- portfolio images
- any public user upload

Do not deploy real production traffic without this volume or an S3-compatible storage replacement.

Optional second volume if you want logs retained on the server:

```text
Mount path: /var/www/html/storage/logs
```

## Environment Variables

Copy `.env.example` into Coolify environment variables, then set real values.

Use these production values:

```dotenv
APP_NAME=Delni
APP_ENV=production
APP_DEBUG=false
APP_URL=https://delni.ly
APP_LOCALE=ar
APP_FALLBACK_LOCALE=ar
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=<coolify-mysql-host-or-service-name>
DB_PORT=3306
DB_DATABASE=delni
DB_USERNAME=delni
DB_PASSWORD=<strong-secret>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public

MAIL_MAILER=resend
RESEND_KEY=<resend-api-key>
MAIL_FROM_ADDRESS=noreply@delni.ly
MAIL_FROM_NAME=Delni

GOOGLE_CLIENT_ID=<google-oauth-client-id>
GOOGLE_CLIENT_SECRET=<google-oauth-client-secret>
GOOGLE_REDIRECT_URL=https://delni.ly/auth/google/callback

CORS_ALLOWED_ORIGINS=https://delni.ly,https://www.delni.ly
TRUSTED_PROXIES=*

SUPER_ADMIN_NAME="Delni Admin"
SUPER_ADMIN_EMAIL=admin@delni.ly
SUPER_ADMIN_PASSWORD=<temporary-strong-admin-password>

WHATSAPP_NUMBER=218911111111
WHATSAPP_BUSINESS_NAME=Delni
```

Generate `APP_KEY` once and store it in Coolify:

```bash
php artisan key:generate --show
```

Never rotate `APP_KEY` after real users exist unless you intentionally handle encrypted/cookie/session invalidation.

## Database

If using Coolify MySQL:

1. Add a MySQL resource in the same Coolify project.
2. Create database/user values matching the app env.
3. Use the internal database host/service name in `DB_HOST`.
4. Enable backups in Coolify.

Before first traffic, run:

```bash
php artisan migrate --force
php artisan delni:setup-admin
php artisan queue:deployment-check
```

Do **not** run demo seeders in production.

## Post-Deploy Command

Configure this as the app's post-deploy command in Coolify:

```bash
php artisan migrate --force && php artisan storage:link && php artisan delni:setup-admin && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache && php artisan queue:restart
```

## Web Service

The Docker image default command starts Nginx + PHP-FPM:

```bash
/usr/bin/supervisord -c /etc/supervisord.conf
```

Coolify should route traffic to port `8080`.

## Queue Worker Service

Create a second Coolify service from the same repository/image.

Command:

```bash
php artisan queue:work --queue=default --max-jobs=100 --max-time=3600 --tries=3
```

Use the same environment variables as the web service.

The queue worker must be running for:

- profile stats recalculation
- review stats updates
- user/profile cleanup jobs

## Scheduler

Preferred Coolify scheduled job:

```bash
php artisan schedule:run
```

Run every minute.

If Coolify scheduled jobs are not available, create a small scheduler service:

```bash
sh -c 'while true; do php artisan schedule:run --no-interaction; sleep 60; done'
```

## Google OAuth

Google Cloud Console must include:

```text
Authorized redirect URI: https://delni.ly/auth/google/callback
```

After changing OAuth settings, test:

1. Public login with Google.
2. Suspended/inactive user rejection.
3. Provider/admin accounts cannot use public Google login.

## Resend / Email

Before launch:

1. Verify `delni.ly` domain in Resend.
2. Add SPF/DKIM DNS records.
3. Confirm `noreply@delni.ly` is allowed.
4. Send a password reset email.
5. Complete the reset flow.

## Libya Spider / DNS Checklist

In Libya Spider DNS:

- `A delni.ly -> <server-ip>`
- `A www.delni.ly -> <server-ip>`

In Coolify:

- Add `delni.ly`.
- Add `www.delni.ly` if you want it served directly, or redirect it to apex.
- Enable HTTPS.

## Backup Checklist

Required before public launch:

- MySQL daily backups.
- Persistent storage volume backup for `storage/app/public`.
- Export Coolify environment variables into a password manager.
- Store Resend, Google OAuth, and admin credentials in a password manager.

## Smoke Test

After deploy:

```bash
php artisan about
php artisan migrate:status
php artisan queue:deployment-check
php artisan queue:failed
php artisan schedule:list
```

Browser checks:

- `https://delni.ly`
- `https://delni.ly/up`
- `https://delni.ly/cp/admin`
- Google login
- password reset email
- provider image upload
- public provider page image rendering

## Final Go / No-Go

Go when all are true:

- Docker build succeeds.
- Post-deploy command succeeds.
- `migrate:status` has no pending migrations.
- Queue worker is running.
- Scheduler runs every minute.
- HTTPS is active.
- Uploads survive a redeploy.
- Resend sends real mail.
- Google OAuth redirects to `https://delni.ly/auth/google/callback`.
