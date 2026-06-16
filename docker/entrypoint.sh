#!/usr/bin/env sh
set -e

mkdir -p storage/app/public storage/app/icons storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if [ ! -L public/storage ]; then
    php artisan storage:link --force >/dev/null 2>&1 || true
fi

chown -R www-data:www-data storage bootstrap/cache public/storage 2>/dev/null || true

exec "$@"
