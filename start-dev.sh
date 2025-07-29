#!/bin/bash

# Set the host (change to your local network IP if needed)
HOST="127.0.0.1"

# Start Laravel backend
cd Dolphin_backend
php artisan serve --host=$HOST &

# Start Vue frontend
cd ../Dolphin_frontend
npm run serve -- --host $HOST
