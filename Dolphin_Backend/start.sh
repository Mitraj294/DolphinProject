#!/usr/bin/env bash
# Render Start Script for Dolphin Backend

set -e  # Exit on error

echo "Starting Dolphin Backend on Render"

# Start the queue worker in the background
echo "Starting queue worker..."
php artisan queue:work --daemon --tries=3 --timeout=90 &

# Start PHP built-in server for Render
echo "Starting PHP web server on port $PORT..."
php -S 0.0.0.0:$PORT -t public public/index.php
