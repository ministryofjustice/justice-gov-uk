FROM php:8-fpm-alpine AS base

RUN apk add --update bash zlib-dev libpng-dev libzip-dev ghostscript icu-dev htop $PHPIZE_DEPS && \
    docker-php-ext-configure intl && \
    docker-php-ext-install exif gd zip mysqli opcache intl && \
    apk del $PHPIZE_DEPS

RUN echo "opcache.jit_buffer_size=500000000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini


## target: dev
FROM base AS dev
RUN apk add --update nano nodejs npm
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# non-root
USER www-data

###


## target: production
FROM base AS build-fpm-composer

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY ./composer.json /var/www/html/composer.json
RUN composer install --no-dev --no-scripts --no-autoloader

COPY . .
RUN composer install --no-dev
RUN composer dump-autoload -o

# non-root
USER www-data

###


FROM base AS build-fpm

WORKDIR /var/www/html
COPY --from=build-fpm-composer /var/www/html /var/www/html

# non-root
USER www-data

###


FROM build-fpm AS test
RUN make test


###


FROM node:20 AS assets-build

WORKDIR /code
COPY . /code/

WORKDIR /code/public/app/themes/justice
RUN npm i
RUN npm run production


###


FROM nginxinc/nginx-unprivileged:1.25-alpine AS nginx

COPY ops/conf/production/php-fpm.conf /etc/nginx/php-fpm.conf
COPY ops/conf/production/server.conf /etc/nginx/conf.d/default.conf
COPY --from=assets-build /code/public /var/www/html/public/
