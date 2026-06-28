# syntax=docker/dockerfile:1

FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js tailwind.config.js ./
RUN npm run build

FROM php:8.3-fpm-alpine AS app
WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        curl \
        curl-dev \
        freetype-dev \
        git \
        icu-dev \
        libjpeg-turbo-dev \
        libxml2-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        nginx \
        oniguruma-dev \
        supervisor \
        unzip \
        zlib-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        dom \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        posix \
        simplexml \
        xml \
        xmlreader \
        xmlwriter \
        zip \
    && sed -i 's/user nginx;/user www-data;/g' /etc/nginx/nginx.conf \
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log \
    && chown -R www-data:www-data /var/lib/nginx /var/log/nginx

# Redis (phpredis) — required for CACHE_STORE / QUEUE_CONNECTION = redis.
# Build deps are installed virtually and removed afterwards to keep the image small.
RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .phpize-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_PROCESS_TIMEOUT=1200

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts

COPY . .
COPY --from=assets /app/public/build ./public/build
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/entrypoint.sh /usr/local/bin/delni-entrypoint
COPY docker/deploy.sh /usr/local/bin/delni-deploy

RUN composer dump-autoload --no-dev --optimize --no-scripts \
    && php artisan package:discover --ansi \
    && php artisan filament:upgrade --ansi \
    && php artisan vendor:publish --force --tag=livewire:assets --ansi \
    && mkdir -p \
        storage/app/public \
        storage/app/icons \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache public \
    && chmod +x /usr/local/bin/delni-entrypoint /usr/local/bin/delni-deploy

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/api/health >/dev/null || exit 1

EXPOSE 8080

ENTRYPOINT ["delni-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
