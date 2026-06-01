#!/usr/bin/env bash
set -e

# Wait for DB (if available)
if [ -f /usr/local/bin/wait-for-db.sh ]; then
  /usr/local/bin/wait-for-db.sh
fi

# Run migrations when requested
if [ "${RUN_MIGRATIONS:-0}" = "1" ]; then
  echo "Running migrations..."
  php artisan migrate --force || true
fi

exec "$@"
