#!/usr/bin/env bash
set -euo pipefail

# Re-create framework dirs (tmpfs mounts are wiped on container start)
mkdir -p /var/www/storage/framework/{cache/data,sessions,views} \
         /var/www/storage/logs \
         /var/www/storage/app/public \
         /var/www/bootstrap/cache

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Run only on the web container; worker overrides CMD and we don't want migrate
# to race when both containers start. Pin migrations to ROLE=web (default).
if [ "${ROLE:-web}" = "web" ]; then
    php artisan migrate --force
    php artisan optimize
    php artisan filament:optimize || true
    php artisan icons:cache || true
    php artisan view:cache || true
fi

exec "$@"
