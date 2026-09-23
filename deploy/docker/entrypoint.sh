#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
    database \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

touch database/database.sqlite
chown -R www-data:www-data database storage bootstrap/cache

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
    php artisan optimize
fi

exec "$@"
