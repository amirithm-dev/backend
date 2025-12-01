FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql zip bcmath

RUN pecl install redis \
    && docker-php-ext-enable redis

WORKDIR /var/www/html
