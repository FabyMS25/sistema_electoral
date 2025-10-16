FROM php:8.3-fpm-alpine

# Install dependencies and PHP extensions
RUN apk add --no-cache \
    libpq-dev \
    git \
    zip \
    unzip \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        gd \
        zip \
        intl

# Set working directory
WORKDIR /var/www/html

# Copy composer and install dependencies
COPY composer.json composer.lock ./
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copy rest of the app
COPY . .

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
