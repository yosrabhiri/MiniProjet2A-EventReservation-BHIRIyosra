#!/bin/sh
set -e

touch .env
mkdir -p var/cache var/log
export APP_ENV="${APP_ENV:-prod}"
export APP_DEBUG="${APP_DEBUG:-0}"
export APP_SECRET="${APP_SECRET:-render-production-secret-change-me}"
export DEFAULT_URI="${DEFAULT_URI:-https://miniprojet-event-reservation.onrender.com}"
export MESSENGER_TRANSPORT_DSN="${MESSENGER_TRANSPORT_DSN:-sync://}"

PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ -n "$JWT_PRIVATE_KEY" ] && [ -n "$JWT_PUBLIC_KEY" ]; then
    mkdir -p /tmp/jwt
    printf '%s' "$JWT_PRIVATE_KEY" > /tmp/jwt/private.pem
    printf '%s' "$JWT_PUBLIC_KEY" > /tmp/jwt/public.pem
    export JWT_SECRET_KEY=/tmp/jwt/private.pem
    export JWT_PUBLIC_KEY=/tmp/jwt/public.pem
fi

if [ -n "$JWT_SECRET_KEY" ] && [ -n "$JWT_PUBLIC_KEY" ] && [ ! -f "$JWT_SECRET_KEY" ]; then
    mkdir -p "$(dirname "$JWT_SECRET_KEY")" "$(dirname "$JWT_PUBLIC_KEY")"
    openssl genrsa -aes256 -passout pass:"$JWT_PASSPHRASE" -out "$JWT_SECRET_KEY" 4096
    openssl rsa -pubout -in "$JWT_SECRET_KEY" -passin pass:"$JWT_PASSPHRASE" -out "$JWT_PUBLIC_KEY"
fi

php bin/console cache:clear --env=prod --no-debug --no-warmup
php bin/console cache:warmup --env=prod --no-debug
php bin/console asset-map:compile --env=prod --no-debug || true

if [ "$RUN_MIGRATIONS" = "1" ]; then
    i=0
    until php bin/console doctrine:query:sql "SELECT 1" --env=prod --no-debug >/dev/null 2>&1; do
        i=$((i + 1))
        if [ "$i" -ge 30 ]; then
            echo "Database is not ready after 30 attempts."
            exit 1
        fi
        sleep 2
    done

    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
fi

chown -R www-data:www-data var public/assets

exec apache2-foreground
