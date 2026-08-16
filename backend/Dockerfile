FROM node:20-bookworm-slim AS node

FROM dunglas/frankenphp:php8.4-bookworm

RUN install-php-extensions pdo_pgsql zip pcntl

RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node for building the Inertia/React frontend assets — copied from the
# official Node image instead of `curl | bash`'ing NodeSource's install
# script, since that live third-party fetch is a flaky build dependency
# (a stall/hang there produces no output, which trips Coolify's build
# idle-timeout and aborts the whole deploy).
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build

EXPOSE 8000 8080 2019

CMD ["sh", "-c", "php artisan migrate --force && php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000 --admin-port=2019"]
