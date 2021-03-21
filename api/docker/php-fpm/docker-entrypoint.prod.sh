#!/usr/bin/env ash
set -eu

echo "Running $INFRA_ENVIRONMENT entrypoint..."
chown -R www-data:www-data /app/var

if [ "$INFRA_ENVIRONMENT" != "prod" ]
then
    rm /app/.env
    mv /app/.env.$INFRA_ENVIRONMENT /app/.env
fi

if [ "$SYMFONY_DECRYPTION_SECRET" != "" ]
then
    echo "- Writing all the decrypted secrets into the .env.$INFRA_ENVIRONMENT.local (To improve performance)..."
    /app/bin/console secrets:decrypt-to-local --force --env=$INFRA_ENVIRONMENT -vvv

    if [ "$INFRA_ENVIRONMENT" != "prod" ]
    then
        mv /app/.env.$INFRA_ENVIRONMENT.local /app/.env.prod.local
    fi
    echo "- Done"
else
    # If running via docker-compose, then it is necessary to wait for the mysql container to be available
    echo "- Installing mysql-client..."
    apk add --no-cache mysql-client
    echo "- Done"

    echo "- Waiting for mysql in host '$DB_SERVER'"
    until mysql -u $DB_USER -p$DB_PASSWORD -h $DB_SERVER --port=$DB_PORT -e "exit" >&2
    do
        echo "-- MySQL is unavailable - sleeping"
        sleep 1
    done
    echo "- Done! MySQL is up - executing command"
fi

echo "- Resolving environment variables..."
composer dump-env prod
echo "- Done"

echo "- Warming up the Symfony cache..."
/app/bin/console cache:warmup --env=prod
echo "- Done"

echo "Done entrypoint!"

exec "$@"