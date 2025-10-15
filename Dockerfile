# ===== Stage 1: Build Composer dependencies =====
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
COPY . .

# ===== Stage 2: Runtime (PHP + FPM) =====
FROM php:8.3-fpm-alpine

# Install required extensions
RUN apk add --no-cache libpq-dev git zip unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Copy app from builder
COPY --from=vendor /app /var/www/html

WORKDIR /var/www/html

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
