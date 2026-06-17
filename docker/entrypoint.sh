#!/usr/bin/env bash
set -e

PORT="${PORT:-8080}"

if ! php -r '$key = getenv("APP_KEY") ?: ""; if (str_starts_with($key, "base64:")) { $key = base64_decode(substr($key, 7), true); } exit(strlen($key) === 32 ? 0 : 1);'; then
    export APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
fi

sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
chown -R www-data:www-data storage bootstrap/cache database

touch database/database.sqlite
chown www-data:www-data database/database.sqlite

php artisan config:clear --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

php artisan storage:link --force --no-interaction || true
php artisan config:cache --no-interaction

exec "$@"
