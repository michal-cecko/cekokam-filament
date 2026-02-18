#!/bin/bash
set -e

# Fix permissions only on mounted volumes (fast, targeted)
chown -R www-data:www-data /var/www/storage/app /var/www/storage/logs 2>/dev/null || true
chmod -R 775 /var/www/storage/app /var/www/storage/logs 2>/dev/null || true

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
