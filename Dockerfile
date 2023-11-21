FROM php:8-fpm-alpine AS base

RUN apk add --update bash zlib-dev libpng-dev libzip-dev htop $PHPIZE_DEPS
RUN docker-php-ext-install exif gd zip mysqli



## target: dev
FROM base AS dev

RUN apk add --update nano nodejs npm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
###



## target: production
FROM base AS build-app-composer

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY ./composer.json /var/www/html/composer.json
RUN composer install --no-dev --no-scripts --no-autoloader

COPY . /var/www/html
RUN composer install --no-dev
RUN composer dump-autoload -o
###


FROM base AS build-app

WORKDIR /var/www/html
COPY --from=build-fpm-composer /var/www/html /var/www/html

###


FROM build-app AS test
RUN make test

###


FROM node:20 AS assets-build

WORKDIR /code
COPY . /code/
RUN npm ci
RUN npm run production

###


FROM nginx:1.25-alpine AS nginx

COPY ops/conf/production/server-prod.conf /etc/nginx/conf.d/default.conf
COPY --from=assets-build /code/web /var/www/html/
