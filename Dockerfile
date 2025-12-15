FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    postgresql-dev \
    mysql-client \
    unzip

# PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
 && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    gd \
    zip \
    intl \
    bcmath \
    opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application
COPY . .

# Permissions
RUN chown -R www-data:www-data /var/www \
 && chmod -R 775 storage bootstrap/cache

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# RUN php artisan migrate --seed --force

EXPOSE 9000
CMD ["php-fpm"]
