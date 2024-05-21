#!/bin/sh

# Run Laravel Artisan commands
php artisan migrate --force
php artisan passport:install --force
php artisan api:install --force

# Execute the CMD provided by the user
exec "$@"
