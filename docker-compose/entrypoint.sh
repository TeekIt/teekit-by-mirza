#!/bin/bash

#################################################################
# Please! run "sudo chmod +x entrypoint.sh" command on your local 
# machine to make the script executable
#################################################################

# Wait for the container to fully start
sleep 5

#################################################################
# Begin - Commands to uncomment only once when the container is build
#################################################################

# # Note: Please! comment the following commands after a successful docker compose build, to avoid re-installations & other basic configurations

# # Remove vendor and composer.lock
# rm -rf /var/www/vendor /var/www/composer.lock
# # Install dependencies
# composer install --no-interaction --prefer-dist --optimize-autoloader
# # Other commands
# php artisan key:generate
# php artisan scout:sync-index-settings
# php artisan scout:import "App\Models\Products"
#################################################################
# End - Commands to uncomment only once when the container is build
#################################################################

# Cache everything at the end
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Keep container running
php-fpm