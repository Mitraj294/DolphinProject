#!/bin/bash

# Set the host (change to your local network IP if needed)
HOST="127.0.0.1"


# Start Laravel backend and ensure it binds to port 8000.
cd Dolphin_Backend
# Desired backend port
BACKEND_PORT=8000

# If something is listening on BACKEND_PORT and is a known dev process, kill it so artisan can bind.
if command -v lsof >/dev/null 2>&1; then
    BACKEND_PID=$(lsof -nP -iTCP:$BACKEND_PORT -sTCP:LISTEN -t || true)
    if [ -n "$BACKEND_PID" ]; then
        CMD=$(ps -p "$BACKEND_PID" -o comm= 2>/dev/null || true)
        if echo "$CMD" | grep -Eqi "php|artisan|php-fpm|node|gunicorn|uwsgi"; then
            echo "Port $BACKEND_PORT is in use by PID $BACKEND_PID ($CMD) — stopping it so backend can bind to $BACKEND_PORT"
            kill "$BACKEND_PID" || true
            sleep 1
            if lsof -nP -iTCP:$BACKEND_PORT -sTCP:LISTEN -t >/dev/null 2>&1; then
                echo "Port $BACKEND_PORT still in use after kill. Backend may fail to start."
            else
                echo "Port $BACKEND_PORT freed for backend."
            fi
        else
            echo "Port $BACKEND_PORT is in use by PID $BACKEND_PID ($CMD). Attempting to free it anyway."
            kill "$BACKEND_PID" || true
            sleep 1
        fi
    fi
fi

# Start artisan on the desired port
php artisan serve --host=$HOST --port=$BACKEND_PORT &

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
# Frontend port (change if you prefer). Default to 8080 so dev URL stays consistent.
FRONTEND_PORT=${FRONTEND_PORT:-8080}

# If the desired port is already in use, try to stop a previous dev server process
if command -v lsof >/dev/null 2>&1; then
    PID=$(lsof -nP -iTCP:$FRONTEND_PORT -sTCP:LISTEN -t || true)
    if [ -n "$PID" ]; then
        # Get the command name for the PID
        CMD=$(ps -p "$PID" -o comm= 2>/dev/null || true)
        if echo "$CMD" | grep -Eqi "node|npm|yarn|vue"; then
            echo "Port $FRONTEND_PORT is in use by PID $PID ($CMD) — stopping previous dev server"
            kill "$PID" || true
            # give it a second to free the port
            sleep 1
            # double-check
            if lsof -nP -iTCP:$FRONTEND_PORT -sTCP:LISTEN -t >/dev/null 2>&1; then
                echo "Port $FRONTEND_PORT still in use after kill; frontend may choose another port."
            else
                echo "Port $FRONTEND_PORT freed."
            fi
        else
            echo "Port $FRONTEND_PORT is in use by PID $PID ($CMD). Will let frontend pick another port."
        fi
    fi
fi

# Start the vue dev server on the chosen port
echo "Starting frontend on 127.0.0.1:$FRONTEND_PORT"
# Suppress Node deprecation warnings (util._extend) for dev output clarity. If you want
# a stack trace to locate the origin, set NODE_OPTIONS=--trace-deprecation instead.
NODE_OPTIONS=--no-deprecation FRONTEND_PORT=$FRONTEND_PORT npm run serve -- --host $HOST --port $FRONTEND_PORT
