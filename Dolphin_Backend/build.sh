#!/usr/bin/env bash
# Render Build Script for Dolphin Backend

set -e  # Exit on error

echo "Starting Dolphin Backend Build on Render"

# Install PHP dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Clear and cache configuration
echo "Optimizing Laravel application..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Install Laravel Passport if not already installed
echo "Setting up Laravel Passport..."
php artisan passport:keys --force || echo "Passport keys already exist"
php artisan passport:install --force --no-interaction || echo "Passport already installed"

# Create storage symlink
echo "Creating storage symlink..."
php artisan storage:link || echo "Storage link already exists"

# Ensure proper permissions
echo "Setting directory permissions..."
chmod -R 775 storage bootstrap/cache || true

echo "Build completed successfully!"
