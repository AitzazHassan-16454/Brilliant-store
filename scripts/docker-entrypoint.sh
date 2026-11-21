#!/bin/bash
set -e

# Default port to 80 if PORT is not set
PORT="${PORT:-80}"

# Adjust Apache port binding for environments like Render/Koyeb ($PORT)
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

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

echo "Starting Apache web server on port ${PORT}..."
exec apache2-foreground
