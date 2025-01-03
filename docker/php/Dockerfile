FROM php:8.2-fpm

# Установка зависимостей для PHP модулей
RUN apt-get update && \
    apt-get install -y zip curl libcurl3-dev libzip-dev libpng-dev libonig-dev libxml2-dev

# Установка дополнительных PHP модулей
RUN docker-php-ext-install curl gd mbstring mysqli pdo pdo_mysql xml zip sockets

# Установка рабочей директории
WORKDIR /var/www/html
COPY . /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer