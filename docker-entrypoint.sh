#!/bin/bash
set -e

# Recreate framework directories inside tmpfs mounts (wiped on every start)
mkdir -p /var/www/storage/framework/cache/data \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/bootstrap/cache

# Fix permissions on mounted volumes
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Run migrations
php artisan migrate --force

# Cache optimization
php artisan optimize
php artisan filament:optimize
php artisan icons:cache
php artisan view:cache

# Start PHP-FPM in background, then Nginx in foreground
php-fpm -D
exec nginx -g 'daemon off;'
