#!/bin/sh
set -e

cd /var/www/html

echo "[entrypoint] Preparing Laravel runtime..."

# If dependencies are not installed yet, do NOT run artisan.
if [ ! -f "vendor/autoload.php" ]; then
  echo "[entrypoint] vendor/autoload.php missing."
  echo "[entrypoint] Run: docker compose run --rm --entrypoint composer laravel install"
  exec php-fpm
fi

# Ensure required directories exist (safe under UID 1000)
echo "[entrypoint] Ensuring runtime directories exist..."
mkdir -p storage/logs \
         storage/framework/cache \
         storage/framework/sessions \
         storage/framework/views \
         bootstrap/cache

# Ensure writable bits (NO chown; bind mount ownership is host-controlled)
echo "[entrypoint] Ensuring writable permissions..."
chmod -R ug+rwX storage bootstrap/cache || true

# Only generate key if not set
if [ -f ".env" ] && ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
  echo "[entrypoint] Generating Laravel APP_KEY..."
  php artisan key:generate --force
fi

# Create storage link if missing
if [ ! -L "public/storage" ]; then
  echo "[entrypoint] Creating storage link..."
  php artisan storage:link || true
fi

echo "[entrypoint] Starting PHP-FPM..."
exec php-fpm
