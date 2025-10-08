#!/bin/bash
set -euo pipefail

echo "Starting deployment process..."

# Run database migrations (don't fail the container if migrations fail temporarily)
echo "Running database migrations..."
php artisan migrate --force || true

# Generate OAuth keys if they don't exist
if [ ! -f "storage/oauth-private.key" ]; then
    echo "Generating OAuth keys..."
    php artisan passport:keys --force || true
fi

# Cache configurations (best-effort)
echo "Caching configurations..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache || true

echo "Deployment startup tasks complete. Launching Apache..."

# Exec so signals are forwarded to apache (PID 1)
exec apache2-foreground
