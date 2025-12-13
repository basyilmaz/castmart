#!/bin/bash
set -e

# Default port
PORT="${PORT:-8000}"

echo "Starting CastMart on port $PORT..."
echo "Working directory: $(pwd)"

# Create required directories
mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views 2>/dev/null || true
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

# Run migrations if database exists
php artisan migrate --force 2>/dev/null || echo "Migration skipped"

# Create storage link
php artisan storage:link 2>/dev/null || true

# Start PHP built-in server directly (not artisan serve)
cd public
exec php -S 0.0.0.0:${PORT} index.php
