#!/bin/sh
set -e

echo "=== Initializing MediTech Container ==="

# Check if vendor directory exists (when mounting code locally via volume)
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Ensure storage & bootstrap cache directories have correct permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Check if .env file exists, otherwise copy from .env.example
if [ ! -f "/var/www/html/.env" ]; then
    echo "Creating .env file from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate application key if not generated yet
if ! grep -q "^APP_KEY=base64:" /var/www/html/.env && ! grep -q "^APP_KEY=[a-zA-Z0-9]" /var/www/html/.env; then
    echo "Generating APP_KEY..."
    php artisan key:generate --no-interaction --force || true
fi

# Create symbolic link for public/storage if not exists
if [ ! -L "/var/www/html/public/storage" ] && [ ! -d "/var/www/html/public/storage" ]; then
    echo "Creating storage symlink..."
    php artisan storage:link --no-interaction || true
fi

# Optional: Run migrations automatically if RUN_MIGRATIONS is set to true
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Waiting for database and running migrations..."
    php artisan migrate --force || echo "Warning: Migration failed or database not ready yet."
fi

echo "=== Starting Container Process ==="
exec "$@"
