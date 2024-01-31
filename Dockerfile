FROM php:8.3-fpm-alpine AS base

RUN apk add --update bash zlib-dev libpng-dev libzip-dev ghostscript icu-dev htop mariadb-client sudo $PHPIZE_DEPS && \
    docker-php-ext-configure intl && \
    docker-php-ext-install exif gd zip mysqli opcache intl && \
    apk del $PHPIZE_DEPS

RUN echo "opcache.jit_buffer_size=500000000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Install wp-cli
RUN curl -o /usr/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x /usr/bin/wp


## target: dev
FROM base AS dev
RUN apk add --update nano nodejs npm
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY deploy/config/local/pool.conf /usr/local/etc/php-fpm.d/pool.conf

# www-data
USER 82


## target: ssh
FROM base AS ssh

RUN apk add --no-cache openssh bash

RUN ssh-keygen -A 
RUN adduser -h /home/ssh-user -s /bin/bash -D ssh-user
RUN echo 'PasswordAuthentication yes' >> /etc/ssh/sshd_config
RUN echo -n "ssh-user:test" | chpasswd
RUN echo 'cd /var/www/html' >> /home/ssh-user/.bash_profile

EXPOSE 22

CMD ["/usr/sbin/sshd", "-D", "-e"]

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

###


FROM base AS build-fpm

WORKDIR /var/www/html
COPY --from=build-fpm-composer --chown=www-data:www-data /var/www/html /var/www/html

# non-root
USER 82

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
RUN rm -rf node_modules


###


FROM nginxinc/nginx-unprivileged:1.25-alpine AS nginx

COPY deploy/config/php-fpm.conf /etc/nginx/php-fpm.conf
COPY deploy/config/server.conf /etc/nginx/conf.d/default.conf
COPY --from=assets-build /code/public /var/www/html/public/
COPY --from=build-fpm-composer /var/www/html/public/wp /var/www/html/public/wp/
