#!/bin/sh
# Disiapkan setiap container start: folder storage, migrasi, cache config.
set -e
cd /var/www/html

for dir in storage/app/public storage/framework/cache/data storage/framework/sessions \
           storage/framework/views storage/logs bootstrap/cache; do
    mkdir -p "$dir"
done
chown -R www-data:www-data storage bootstrap/cache

php artisan package:discover --ansi
php artisan storage:link >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
