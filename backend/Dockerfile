# Multi-stage: `assets` builds the frontend with Node, `vendor` installs the
# production Composer packages, and the final stage copies out only what the
# running app reads. No Node, Composer, frontend sources, tests or docs ship.

FROM dunglas/frankenphp:php8.4-bookworm AS php

RUN install-php-extensions pdo_pgsql zip pcntl

WORKDIR /var/www/html

FROM node:20-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.ts tsconfig.json tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

FROM php AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
# Runs post-autoload-dump too (package:discover), which caches prod-only providers in bootstrap/cache.
RUN composer dump-autoload --no-dev --optimize

FROM php

# composer.json stays: Laravel reads the app namespace from it.
COPY --from=vendor /var/www/html/artisan /var/www/html/composer.json ./
COPY --from=vendor /var/www/html/app ./app
COPY --from=vendor /var/www/html/bootstrap ./bootstrap
COPY --from=vendor /var/www/html/config ./config
COPY --from=vendor /var/www/html/database ./database
COPY --from=vendor /var/www/html/routes ./routes
COPY --from=vendor /var/www/html/storage ./storage
COPY --from=vendor /var/www/html/resources/views ./resources/views
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=vendor /var/www/html/public ./public
COPY --from=assets /app/public/build ./public/build

EXPOSE 8000 8080 2019

CMD ["sh", "-c", "php artisan migrate --force && php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000 --admin-port=2019"]
