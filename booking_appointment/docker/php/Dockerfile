FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev libicu-dev zip \
    && docker-php-ext-install pdo pdo_mysql intl opcache zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html
