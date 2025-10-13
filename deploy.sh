#!/bin/bash
cd /var/www/html/livestock-m-e-backend

# Pull latest changes
git pull origin main

# Install PHP dependencies
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate
# php artisan migrate --force

# Clear cache
php artisan optimize:clear
php artisan optimize
php artisan route:cache
php artisan view:cache
php artisan config:clear

# Set proper permissions
# echo "Setting permissions..."
# chmod -R 755 storage bootstrap/cache
# chown -R www-data:www-data storage bootstrap/cache

# Restart services (optional - uncomment if needed)
# echo "Restarting Apache..."
# sudo systemctl reload apache2

echo "Deployment completed successfully!"
