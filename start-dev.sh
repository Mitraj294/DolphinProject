#!/bin/bash

# Set the host (change to your local network IP if needed)
HOST="127.0.0.1"


# Start Laravel backend
cd Dolphin_Backend
php artisan serve --host=$HOST &

# Ensure Laravel scheduler cron job is present
CRON_ENTRY="* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
CRON_EXISTS=$(crontab -l 2>/dev/null | grep -F "$CRON_ENTRY" || true)
if [ -z "$CRON_EXISTS" ]; then
    (crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -
    echo "Laravel scheduler cron job added."
else
    echo "Laravel scheduler cron job already present."
fi

# Start Vue frontend
cd ../Dolphin_Frontend
npm run serve -- --host $HOST
