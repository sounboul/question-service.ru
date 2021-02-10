# docker/php-fpm/Dockerfile

# PHP-FPM 7.4
FROM php:7.4-fpm-alpine

# Работа от пользователя
ARG USER_UID
ARG USER_GID
RUN apk add --no-cache shadow
RUN usermod -u ${USER_UID} www-data && groupmod -g ${USER_GID} www-data

COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# Установка пакетов
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN apk --update --no-cache add git bash

# Установка PHP расширений
RUN apk add --no-cache --repository http://dl-3.alpinelinux.org/alpine/edge/testing gnu-libiconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

RUN apk add --update --no-cache \
    libzip-dev \
    libpng-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    libsodium-dev

RUN docker-php-ext-install \
    opcache \
    zip \
    intl \
    pgsql \
    pdo_pgsql \
    gd \
    sysvsem

RUN apk add \
        --repository http://dl-cdn.alpinelinux.org/alpine/v3.6/main \
        --no-cache \
        rabbitmq-c-dev $PHPIZE_DEPS \
        && pecl install amqp \
        && docker-php-ext-enable amqp

RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Установка jpegoptiom + optipng
RUN apk update && apk add --upgrade jpegoptim optipng

# Установка workdir
WORKDIR /var/www

# Установка supervisor
RUN apk add --no-cache supervisor
COPY supervisor.conf /etc/supervisor.conf

# Запуск
EXPOSE 9000
CMD ["supervisord", "-c", "/etc/supervisor.conf"]
