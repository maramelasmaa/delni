#!/usr/bin/env sh
set -e

mkdir -p storage/app/public storage/app/icons storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

php artisan storage:link --force >/dev/null 2>&1 || true

if [ "$(readlink public/storage 2>/dev/null || true)" != "/var/www/html/storage/app/public" ]; then
    rm -f public/storage
    ln -s /var/www/html/storage/app/public public/storage
fi

php artisan migrate --force --no-interaction >/dev/null 2>&1 || true
php artisan delni:setup-admin --no-interaction >/dev/null 2>&1 || true

chown -R www-data:www-data storage bootstrap/cache public/storage 2>/dev/null || true

exec "$@"
