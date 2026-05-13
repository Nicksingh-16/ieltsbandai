#!/bin/bash
# Production deploy script for ieltsbandai on the shared VPS.
#
# VPS constraint: PHP 8.1 is the system default (used by other projects on
# this box). PHP 8.3 is installed alongside. Always invoke `php8.3` and
# `php8.3 $(which composer)` — never bare `php` or `composer`.
#
# Run manually:  bash /root/deploy-ieltsbandai.sh
# Triggered automatically by GitHub Actions on push to main (.github/workflows/deploy.yml).

set -e

cd /var/www/ieltsbandai

git fetch origin main
git reset --hard origin/main

COMPOSER_ALLOW_SUPERUSER=1 php8.3 $(which composer) install --no-dev --optimize-autoloader --no-interaction

npm ci
npm run build

php8.3 artisan migrate --force
php8.3 artisan db:seed --force

php8.3 artisan config:clear
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache

chown -R www-data:www-data /var/www/ieltsbandai
chmod -R 775 /var/www/ieltsbandai/storage /var/www/ieltsbandai/bootstrap/cache

systemctl reload php8.3-fpm
supervisorctl -c /etc/supervisor/supervisord.conf restart ieltsbandai-worker:*

echo "Deploy complete at $(date)"
