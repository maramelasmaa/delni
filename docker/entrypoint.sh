#!/usr/bin/env sh
set -e

mkdir -p storage/app/public storage/app/icons storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache && php artisan storage:link --force
php scripts/update-sw-version.php 2>/dev/null || true

if [ "$(readlink public/storage 2>/dev/null || true)" != "/var/www/html/storage/app/public" ]; then
    rm -f public/storage
    ln -s /var/www/html/storage/app/public public/storage
fi

# Migrations and production seeding (idempotent — safe on every container start)
php artisan migrate --force --no-interaction || true
php artisan db:seed --class=RoleSeeder --force --no-interaction || true
php artisan delni:ensure-super-admin --no-interaction || true
php artisan db:seed --class=DemoSeeder --force --no-interaction || true

# Rebuild Laravel bootstrap caches
# route:cache is intentionally excluded — web.php uses closure routes which cannot be serialized
php artisan optimize:clear
php artisan config:cache
php artisan view:cache || true
php artisan event:cache || true

# Signal any running queue workers to reload their config
php artisan queue:restart || true

chown -R www-data:www-data storage bootstrap/cache public/storage 2>/dev/null || true

exec "$@"
