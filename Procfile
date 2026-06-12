release: php artisan migrate --force && php artisan storage:link && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan queue:work --queue=default --max-jobs=100 --max-time=3600
