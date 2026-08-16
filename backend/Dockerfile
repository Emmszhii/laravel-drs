FROM dunglas/frankenphp:php8.4-bookworm

RUN install-php-extensions pdo_pgsql zip pcntl

RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node for building the Inertia/React frontend assets
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build

EXPOSE 8000 8080 2019

CMD ["sh", "-c", "php artisan migrate --force && php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000 --admin-port=2019"]
