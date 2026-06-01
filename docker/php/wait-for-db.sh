#!/usr/bin/env bash
set -e

host="${DB_HOST:-db}"
port="${DB_PORT:-3306}"
timeout="${DB_WAIT_TIMEOUT:-30}"

echo "Waiting for DB at ${host}:${port} (timeout ${timeout}s)..."
for i in $(seq 1 ${timeout}); do
  if (echo > /dev/tcp/${host}/${port}) >/dev/null 2>&1; then
    echo "DB reachable"
    exit 0
  fi
  sleep 1
done

echo "Timed out waiting for DB at ${host}:${port}" >&2
exit 1
