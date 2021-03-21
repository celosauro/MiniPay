#!/usr/bin/env sh
set -eu

command="$@"

echo "envvbvvvvvv"
env
echo "Waiting for mysql in host '$DB_SERVER'"
until mysql -u $DB_USER -p$DB_PASSWORD -h $DB_SERVER --port=$DB_PORT -e "exit" >&2
do
  echo "MySQL is unavailable - sleeping"
  sleep 1
done

echo "MySQL is up - executing command"

exec $command
