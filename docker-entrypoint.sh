#!/bin/bash
set -e

echo "Starting Dolphin Backend on Render..."

# Wait for database to be ready
echo "Waiting for database..."
DB_PORT=${DB_PORT:-3306}
until timeout 1 bash -c "cat < /dev/null > /dev/tcp/$DB_HOST/$DB_PORT" 2>/dev/null; do
  echo "$DB_HOST:$DB_PORT - no response"
  echo "Database is unavailable - sleeping"
  sleep 5
done
echo "Database is ready!"

# Run migrations FIRST (before cache operations)
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Clear caches (safe to do after migrations)
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear || echo "Cache table not ready yet, skipping..."
php artisan view:clear
php artisan route:clear

# Cache configuration for production
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

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
