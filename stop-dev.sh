#!/bin/bash

echo " Stopping Dolphin development environment..."

# Remove the Laravel scheduler cron job
CRON_ENTRY="* * * * * cd /home/digilab/Dolphin/Dolphin_Backend && php artisan schedule:run >> /dev/null 2>&1"
echo "Removing Laravel scheduler cron job..."
crontab -l 2>/dev/null | grep -v "$CRON_ENTRY" | crontab - 2>/dev/null || true
echo " Cron job removed"

# Kill Laravel backend processes
echo "Stopping Laravel backend..."
pkill -f "php artisan serve" || true

# Kill Vue frontend processes  
echo "Stopping Vue frontend..."
pkill -f "npm run serve" || true
pkill -f "vue-cli-service" || true

# Kill any remaining Node processes related to the frontend
pkill -f "node.*8080" || true

echo "All Dolphin development servers stopped"
echo "Tip: Use ./start-dev.sh to restart the development environment"