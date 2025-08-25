#!/bin/bash
cd /var/www/laravel

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate
# php artisan migrate --force

# Clear cache
php artisan optimize:clear
php artisan optimize

# Restart services
# sudo systemctl reload apache2
