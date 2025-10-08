#!/bin/bash
set -e

echo "Starting deployment process..."

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Generate OAuth keys if they don't exist
if [ ! -f "storage/oauth-private.key" ]; then
    echo "Generating OAuth keys..."
    php artisan passport:keys --force
fi

# Cache configurations
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache

echo "Deployment complete!"

# Start Apache
apache2-foreground
