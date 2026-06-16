release: php artisan migrate --force && php artisan storage:link && php artisan delni:setup-admin && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
web: php artisan serve --host=0.0.0.0 --port=$PORT
worker: php artisan queue:work --queue=default --max-jobs=100 --max-time=3600
