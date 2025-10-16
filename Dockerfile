FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    libpq-dev \
    git \
    zip \
    unzip \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    postgresql-client \ 
    && docker-php-ext-install \
        pdo_pgsql \
        gd \
        zip \
        intl

# Set working directory
WORKDIR /var/www/html

# Copy all project files first
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install dependencies (now artisan exists)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Fix permissions for Laravel storage/bootstrap
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 9000 and start PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]
