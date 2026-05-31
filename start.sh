#!/usr/bin/env bash

mkdir -p /var/www/html/database

if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

php artisan migrate --force
php artisan db:seed --force

apache2-foreground