FROM php:8.4-fpm-alpine

# Add PHP extension installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

# Install system dependencies & PHP extensions in one layer
RUN apk add --no-cache bash nginx curl git zip unzip npm nodejs imap-dev && \
    install-php-extensions pdo_pgsql pdo_mysql exif pcntl bcmath gd zip gmp opcache intl redis imap && \
    mkdir -p /var/run/php

# OPcache config
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

ENV PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=100000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=192 \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE=10

# Copy PHP, PHP-FPM pool & Nginx config early (rarely changes = better cache)
COPY ./php.ini /usr/local/etc/php/php.ini
COPY ./php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./nginx.conf /etc/nginx/nginx.conf

# Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# 1) Copy dependency manifests first for layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

COPY package.json package-lock.json ./
RUN npm ci

# 2) Copy the rest of the application
COPY . /var/www

# 3) Finish composer (autoloader + post-scripts) & build frontend
RUN composer dump-autoload --optimize && \
    mv .env.prod .env && \
    rm -rf /var/www/public/storage && \
    ln -s /var/www/storage/app/public /var/www/public/storage && \
    npm run build && \
    rm -rf node_modules

# Set permissions at build time (not at runtime)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
