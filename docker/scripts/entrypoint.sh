#!/bin/bash

echo "Waiting for MySQL..."
until nc -z -v -w30 mysql 3306; do
   echo "Waiting for MySQL database connection..."
   sleep 5
done
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

php artisan config:cache

php artisan migrate --force

php artisan db:seed --force

while true; do
    php artisan schedule:run --verbose --no-interaction &
    sleep 10
done &

exec php-fpm
