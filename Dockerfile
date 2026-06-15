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

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./

RUN composer install --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader

COPY . .

RUN mkdir -p var/cache var/log

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]
