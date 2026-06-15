FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql zip intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

COPY . .

RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader \
    && grep -q "vendor/autoload.php" public/index.php \
    && ! grep -q "autoload_runtime.php" public/index.php \
    && mkdir -p var/cache var/log

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]
