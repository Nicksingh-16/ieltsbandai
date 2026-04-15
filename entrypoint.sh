#!/bin/bash
set -e

# Substitute the runtime PORT into Apache config (Render injects $PORT at runtime)
PORT=${PORT:-80}
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Laravel startup
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --force 2>/dev/null || true
php artisan config:cache

# Start supervisor (manages Apache + queue worker)
exec supervisord -c /var/www/html/supervisord.conf
