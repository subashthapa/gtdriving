FROM php:8.4-cli AS backend-deps

WORKDIR /app
RUN apt-get update \
    && apt-get install -y --no-install-recommends git libonig-dev unzip \
    && docker-php-ext-install -j"$(nproc)" mbstring \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --optimize-autoloader \
        --prefer-dist

FROM node:22-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY --from=backend-deps /app/vendor/tightenco/ziggy ./vendor/tightenco/ziggy
RUN npm run build

FROM php:8.4-apache AS production

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl libfreetype6-dev libjpeg62-turbo-dev libonig-dev libpng-dev libsqlite3-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd mbstring opcache pdo_sqlite zip \
    && a2enmod headers rewrite \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && sed -ri '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' \
        /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .
COPY --from=backend-deps /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY deploy/docker/php.ini /usr/local/etc/php/conf.d/production.ini
COPY deploy/docker/entrypoint.sh /usr/local/bin/gtdriving-entrypoint

RUN mkdir -p \
        database \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && chmod +x /usr/local/bin/gtdriving-entrypoint \
    && chown -R www-data:www-data database storage bootstrap/cache \
    && php artisan package:discover --ansi \
    && php artisan storage:link

ENTRYPOINT ["gtdriving-entrypoint"]
CMD ["apache2-foreground"]

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl --fail --silent --show-error http://localhost/up || exit 1
