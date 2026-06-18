release: php artisan migrate --force && php artisan db:seed --class=RoleSeeder --force --no-interaction && php artisan delni:ensure-super-admin --no-interaction && php artisan optimize:clear && php artisan config:cache && php artisan view:cache && php artisan event:cache
web: mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs && php artisan storage:link --force && php artisan serve --host=0.0.0.0 --port=$PORT
worker: php artisan queue:work --queue=default --tries=3 --timeout=90 --sleep=3 --max-jobs=500 --max-time=3600
scheduler: sh -c "while true; do php artisan schedule:run --no-interaction; sleep 60; done"
