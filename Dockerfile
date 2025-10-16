FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    libpq-dev \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    postgresql-client \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-configure gd --with-jpeg --with-webp
RUN docker-php-ext-install \
    pdo_pgsql \
    pdo_mysql \
    gd \
    zip \
    intl \
    mbstring \
    exif \
    pcntl \
    bcmath \
    opcache

# Install and configure Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create working directory
WORKDIR /var/www/html

# Copy ALL application files first
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Fix permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]