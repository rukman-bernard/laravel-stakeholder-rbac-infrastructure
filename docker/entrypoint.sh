#!/bin/sh
set -e

# Print a clear marker that entrypoint executed (ASCII only)
echo "[entrypoint] $(date '+%Y-%m-%d %H:%M:%S %z') - entrypoint.sh executed"

cd /var/www/html

echo "[entrypoint] Preparing Laravel runtime..."

# Ensure .env exists
if [ ! -f ".env" ]; then
  echo "[entrypoint] Creating .env from example..."
  cp .env.example .env
fi

# Ensure runtime directories exist (BEFORE composer ever runs)
echo "[entrypoint] Ensuring runtime directories exist..."
mkdir -p \
  storage/logs \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  bootstrap/cache

# Ensure writable bits
chmod -R ug+rwX storage bootstrap/cache || true

# If dependencies missing, stop here (do NOT start artisan yet)
if [ ! -f "vendor/autoload.php" ]; then
  echo "[entrypoint] Dependencies missing."
  echo "[entrypoint] Run: docker compose exec laravel composer install"
  exec php-fpm
fi

# Generate key if needed
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
  echo "[entrypoint] Generating Laravel APP_KEY..."
  php artisan key:generate --force
fi

# Create storage link
if [ ! -L "public/storage" ]; then
  php artisan storage:link || true
fi

echo "[entrypoint] Starting PHP-FPM..."
exec php-fpm
