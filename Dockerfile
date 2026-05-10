# syntax=docker/dockerfile:1

# ---- builder ----
FROM php:8.4-cli-alpine AS builder

RUN apk add --no-cache bash git curl unzip nodejs npm imap-dev linux-headers \
    && curl -sSLf -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions pdo_pgsql pdo_mysql exif pcntl bcmath gd zip gmp opcache intl redis imap sockets

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Dependency manifests first for cache layer reuse
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

# App source
COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative \
    && rm -rf public/storage \
    && ln -s /var/www/storage/app/public public/storage \
    && npm run build \
    && rm -rf node_modules

# RoadRunner binary (Linux/amd64 inside the build container)
RUN php ./vendor/bin/rr get-binary --location /usr/local/bin


# ---- runtime ----
FROM php:8.4-cli-alpine AS runtime

RUN apk add --no-cache bash curl tini imap-dev \
    && curl -sSLf -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions pdo_pgsql pdo_mysql exif pcntl bcmath gd zip gmp opcache intl redis imap sockets \
    && rm /usr/local/bin/install-php-extensions

COPY php.ini /usr/local/etc/php/php.ini
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

ENV PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=100000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=192 \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE=10

WORKDIR /var/www

COPY --from=builder /var/www /var/www
COPY --from=builder /usr/local/bin/rr /usr/local/bin/rr

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Dokploy's docker terminal hardcodes `docker exec -w /`, overriding WORKDIR.
# Auto-cd into the app dir for interactive shells (bash/sh/ash, login or not).
RUN printf 'cd /var/www\n' > /etc/profile.d/cd-app.sh \
    && printf 'cd /var/www\n' > /root/.bashrc \
    && printf 'cd /var/www\n' > /root/.ashrc
ENV ENV=/root/.ashrc

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl -fsS http://localhost:8000/up || exit 1

ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/docker-entrypoint.sh"]
CMD ["php", "artisan", "octane:start", "--server=roadrunner", "--host=0.0.0.0", "--port=8000", "--workers=auto", "--max-requests=500"]
