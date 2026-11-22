#!/bin/sh
# Applies migrations (and demo data when APP_DEMO=1) before starting Apache.
set -e

php bin/console migrate

if [ "${APP_DEMO:-0}" = "1" ] && [ "${APP_ENV:-production}" = "production" ]; then
    echo "APP_DEMO ignored: demo data is never loaded when APP_ENV=production." >&2
elif [ "${APP_DEMO:-0}" = "1" ]; then
    php bin/console seed
    php bin/console demo:schedule
fi

exec "$@"
