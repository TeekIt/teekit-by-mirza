#!/bin/bash
set -e

# Wait for the container to fully start
sleep 10

#################################################################
# Begin - Commands to run only once when the container is build
#################################################################
# Remove vendor and composer.lock
# rm -rf /var/www/vendor /var/www/composer.lock
# # Install dependencies
# composer install --optimize-autoloader
# # Other commands
# php artisan key:generate
# php artisan scout:sync-index-settings
# php artisan scout:import "App\Products"
#################################################################
# End - Commands to run only once when the container is build
#################################################################

# Cache everything at the end
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Keep container running
php-fpm