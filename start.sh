#!/usr/bin/env bash

php artisan migrate --force 2>/dev/null || echo "⚠ migrate skipped (DB unavailable)"
php artisan db:seed --force 2>/dev/null || echo "⚠ seed skipped (DB unavailable)"

apache2-foreground