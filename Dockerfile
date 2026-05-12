# PHP 8.2 + Apache base.
FROM php:8.2-apache

# ──────────────────────────────────────────────────────────────────────────
# System deps. Important: we DO NOT use Debian's `apt-get install nodejs npm`
# because on Debian Trixie that pulls ~300 packages (libllvm19, mesa-gallium,
# eslint, handlebars, etc.) totalling close to 1GB — Render's build sandbox
# runs out of disk well before the build finishes.
# Instead we install a slim Node.js from NodeSource (1 package, ~50MB).
# ──────────────────────────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    ca-certificates \
    gnupg \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    supervisor \
 && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get install -y --no-install-recommends nodejs \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP extensions Laravel needs (pdo_pgsql for Render Postgres, gd for image work).
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Apache: serve /public, enable rewrites.
RUN a2enmod rewrite \
 && sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Composer binary (faster than installing globally).
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy app source.
COPY . .

# Storage / cache writable.
RUN chmod -R 775 storage bootstrap/cache \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# PHP deps (prod only). --no-scripts skips package:discover during install;
# we run it explicitly below so a discovery failure (e.g. transient Sentry SDK
# auto-wiring) doesn't kill the whole build silently.
RUN composer install --no-dev --no-scripts --optimize-autoloader --prefer-dist \
 && composer dump-autoload --optimize --no-dev \
 && php artisan package:discover --ansi

# Frontend build.
RUN npm ci --no-audit --no-fund \
 && npm run build \
 && rm -rf node_modules ~/.npm

# Entrypoint substitutes PORT at runtime, then runs migrations + supervisor.
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
