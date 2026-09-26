#!/bin/sh
set -eu

cd /var/www/html

database_file="${DB_DATABASE:-/var/lib/gtdriving/database.sqlite}"
database_directory="$(dirname "$database_file")"

mkdir -p \
    "$database_directory" \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

touch "$database_file"
chown -R www-data:www-data "$database_directory" storage bootstrap/cache

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
    php artisan optimize
fi

exec "$@"
