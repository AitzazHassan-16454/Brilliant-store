#!/bin/bash
set -e

# Default port to 7860 (Hugging Face default) or Render/Koyeb port
PORT="${PORT:-7860}"

# Create storage symlink if not already linked
php artisan storage:link --no-interaction || true

# Run database migrations if database connection is available
if [ -n "$DB_HOST" ] || [ -n "$DATABASE_URL" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --no-interaction || echo "Migration skipped or failed; continuing startup..."
fi

# Cache configuration, routes, and views for production performance
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting web server on port ${PORT}..."
if [ "$(id -u)" -eq 0 ]; then
    sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf 2>/dev/null || true
    sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true
    exec apache2-foreground
else
    exec php artisan serve --host=0.0.0.0 --port="${PORT}"
fi

