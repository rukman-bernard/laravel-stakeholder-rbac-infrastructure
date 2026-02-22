#!/bin/sh
set -eu

# ------------------------------------------------------------
# Vite Node Container Entrypoint
#
# Purpose:
# - Install Node dependencies once per fresh node_modules volume
# - Remove stale Laravel/Vite hot file on restarts
# - Start the Vite dev server on a stable host/port
#
# Adjustables:
# - VITE_HOST / VITE_PORT / STRICT_PORT
# ------------------------------------------------------------

APP_DIR="/var/www/html"
PUBLIC_DIR="$APP_DIR/public"
HOT_FILE="$PUBLIC_DIR/hot"

VITE_HOST="${VITE_HOST:-0.0.0.0}"
VITE_PORT="${VITE_PORT:-5173}"
STRICT_PORT="${STRICT_PORT:-true}"

MAX_WAIT_LOOPS="${MAX_WAIT_LOOPS:-20}"
WAIT_SLEEP_SECONDS="${WAIT_SLEEP_SECONDS:-0.5}"

log() { echo "[vite-node] $*"; }

cd "$APP_DIR"

# ------------------------------------------------------------
# Install dependencies (only when node_modules is missing/empty)
# ------------------------------------------------------------
if [ ! -d "node_modules" ] || [ -z "$(find node_modules -mindepth 1 -maxdepth 1 2>/dev/null | head -n 1)" ]; then
  log "node_modules is missing/empty. Installing dependencies..."

  # Prefer deterministic installs when a lockfile exists
  if [ -f "package-lock.json" ]; then
    npm ci
  else
    npm install
  fi
fi

# ------------------------------------------------------------
# Remove stale hot file (prevents Laravel pointing to dead Vite)
# ------------------------------------------------------------
if [ -f "$HOT_FILE" ]; then
  log "Removing stale hot file: $HOT_FILE"
  rm -f "$HOT_FILE"
fi

# ------------------------------------------------------------
# Wait until public/ is writable (bind mount ready)
# ------------------------------------------------------------
i=0
while :; do
  if [ -d "$PUBLIC_DIR" ] && [ -w "$PUBLIC_DIR" ]; then
    break
  fi

  if [ "$i" -ge "$MAX_WAIT_LOOPS" ]; then
    log "ERROR: public/ is not writable after $MAX_WAIT_LOOPS checks."
    log "Fix permissions / user mapping for the bind mount."
    exit 1
  fi

  log "Waiting for public/ to be writable..."
  i=$((i + 1))
  sleep "$WAIT_SLEEP_SECONDS"
done

# ------------------------------------------------------------
# Start Vite dev server
# ------------------------------------------------------------
VITE_ARGS="--host $VITE_HOST --port $VITE_PORT"
if [ "$STRICT_PORT" = "true" ]; then
  VITE_ARGS="$VITE_ARGS --strictPort"
fi

log "Starting Vite dev server: vite $VITE_ARGS"
exec npm run dev -- $VITE_ARGS