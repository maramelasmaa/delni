#!/usr/bin/env sh
set -e

mkdir -p storage/app/public storage/app/icons storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# CONTAINER_ROLE controls which setup steps run.
# app       → per-container runtime setup: storage link + cache warm-up (idempotent, safe under multiple replicas)
# deploy    → one-shot release tasks: schema migration + idempotent seeding (run by ONE container before app swap)
# worker    → skip setup; just exec queue:work via CMD
# scheduler → skip setup; just exec schedule loop via CMD
#
# NOTE: migrations DO NOT run in the "app" role. Running `migrate` on every app boot is
# unsafe during rolling deploys (two app containers migrating concurrently can deadlock).
# Migrations run once via the "deploy" role — wired as a one-shot compose `migrate` service
# and as the Coolify "Pre-deployment Command" (`delni-deploy`).
CONTAINER_ROLE="${CONTAINER_ROLE:-app}"

if [ "$CONTAINER_ROLE" = "deploy" ]; then
    exec delni-deploy
fi

if [ "$CONTAINER_ROLE" = "app" ]; then
    php artisan storage:link --force

    if [ "$(readlink public/storage 2>/dev/null || true)" != "/var/www/html/storage/app/public" ]; then
        rm -f public/storage
        ln -s /var/www/html/storage/app/public public/storage
    fi

    php artisan optimize:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    php artisan queue:restart || true
fi

exec "$@"
