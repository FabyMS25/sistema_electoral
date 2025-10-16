# =========================================================
# STAGE 1 — PHP dependencies (Composer)
# =========================================================
FROM php:8.3-fpm-alpine AS app

# Install dependencies & GD
RUN apk add --no-cache \
    libpq-dev \
    git \
    zip \
    unzip \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    oniguruma-dev \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql mbstring

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (for layer caching)
COPY composer.json composer.lock ./

# Install composer dependencies (no-dev)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy all project files
COPY . .

# Fix permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# =========================================================
# STAGE 2 — Nginx (optional, if you serve via nginx)
# =========================================================
# You can skip this part if you use php-fpm directly in your container

# EXPOSE 9000
CMD ["php-fpm"]
