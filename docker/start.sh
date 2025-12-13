#!/bin/bash
set -e

# Default port
PORT="${PORT:-8000}"

echo "Starting CastMart on port $PORT..."

# Run migrations if database exists
php artisan migrate --force 2>/dev/null || echo "Migration skipped"

# Create storage link
php artisan storage:link 2>/dev/null || true

# Start server
exec php artisan serve --host=0.0.0.0 --port="$PORT"
