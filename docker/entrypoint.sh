#!/bin/sh
set -eu

APP_DIR="/var/www/html"
ENV_FILE="$APP_DIR/.env"
ENV_EXAMPLE="$APP_DIR/.env.example"
AUTOLOAD="$APP_DIR/vendor/autoload.php"
STORAGE_LINK="$APP_DIR/public/storage"

log() {
  # ASCII only, stable prefix for grep
  echo "[entrypoint] $*"
}

log "$(date '+%Y-%m-%d %H:%M:%S %z') - entrypoint.sh executed"

cd "$APP_DIR"

log "Preparing Laravel runtime..."

# ------------------------------------------------------------
# Ensure .env exists (dev-friendly default)
# ------------------------------------------------------------
if [ ! -f "$ENV_FILE" ] && [ -f "$ENV_EXAMPLE" ]; then
  log "Creating .env from .env.example..."
  cp "$ENV_EXAMPLE" "$ENV_FILE"
fi

# ------------------------------------------------------------
# Ensure runtime directories exist BEFORE any artisan usage
# ------------------------------------------------------------
log "Ensuring runtime directories exist..."
mkdir -p \
  storage/logs \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  bootstrap/cache

# ------------------------------------------------------------
# Best-effort permissions (bind mounts may limit chmod)
# ------------------------------------------------------------
log "Ensuring runtime directories have consistent permissions (best-effort)..."
find storage bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null || true
find storage bootstrap/cache -type f -exec chmod 664 {} \; 2>/dev/null || true
find storage/app/public -type f -exec chmod 644 {} \; 2>/dev/null || true


# ------------------------------------------------------------
# If dependencies are missing, do not run artisan.
# Keep PHP-FPM running so the container is usable.
# ------------------------------------------------------------
if [ ! -f "$AUTOLOAD" ]; then
  log "Dependencies missing (vendor/ not installed)."
  log "Run: docker compose exec laravel composer install"
  log "Starting PHP-FPM anyway..."
  exec php-fpm
fi

# ------------------------------------------------------------
# Generate APP_KEY if missing (safe only when artisan is available)
# ------------------------------------------------------------
if [ -f "$ENV_FILE" ]; then
  if ! grep -q '^APP_KEY=base64:' "$ENV_FILE" 2>/dev/null; then
    log "Generating Laravel APP_KEY..."
    php artisan key:generate --force
  fi
else
  log "WARNING: .env not found. Skipping APP_KEY generation."
fi

# ------------------------------------------------------------
# Storage symlink (idempotent)
# ------------------------------------------------------------
if [ ! -L "$STORAGE_LINK" ]; then
  log "Creating storage symlink (public/storage)..."
  php artisan storage:link >/dev/null 2>&1 || true
fi

log "Starting PHP-FPM..."
exec php-fpm