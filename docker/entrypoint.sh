#!/bin/sh
# Applies migrations (and demo data when APP_DEMO=1) before starting Apache.
set -e

php bin/console migrate

if [ "${APP_DEMO:-0}" = "1" ]; then
    php bin/console seed
    php bin/console demo:schedule
fi

exec "$@"
