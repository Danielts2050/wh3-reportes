#!/usr/bin/env bash

touch /tmp/database.sqlite

chmod 777 /tmp/database.sqlite

php artisan migrate --force
php artisan db:seed --force

apache2-foreground