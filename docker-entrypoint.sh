#!/bin/bash
set -e

echo "Starting Dolphin Backend on Render..."

# Wait for database to be ready
echo "Waiting for database..."
until pg_isready -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" 2>/dev/null; do
  echo "Database is unavailable - sleeping"
  sleep 2
done
echo "Database is ready!"

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Set up Passport
echo "Setting up Laravel Passport..."
php artisan passport:keys --force 2>/dev/null || echo "Passport keys already exist"
php artisan passport:install --force || echo "Passport already installed"

# Create storage link
php artisan storage:link 2>/dev/null || echo "Storage link already exists"

# Start queue worker in background
echo "Starting queue worker..."
php artisan queue:work --daemon --tries=3 --timeout=90 &

# Start PHP built-in server
echo "Starting web server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
