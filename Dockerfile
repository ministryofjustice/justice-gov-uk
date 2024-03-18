FROM ministryofjustice/wordpress-base-fpm:latest AS base-fpm

###

FROM nginxinc/nginx-unprivileged:1.25-alpine AS base-nginx

USER root

COPY deploy/config/init/* /docker-entrypoint.d/
RUN chmod +x /docker-entrypoint.d/*
RUN echo "# This file is configured at runtime." > /etc/nginx/real_ip.conf

USER 82


## target: dev
FROM base-fpm AS dev
RUN apk add --update nano nodejs npm inotify-tools

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# www-data
USER 82



## target: local-ssh
FROM base-fpm AS local-ssh

ARG LOCAL_SSH_PASSWORD

RUN apk add --no-cache openssh bash

RUN ssh-keygen -A && \
    adduser -h /home/ssh-user -s /bin/bash -D ssh-user && \
    echo 'PasswordAuthentication yes' >> /etc/ssh/sshd_config && \
    echo -n "ssh-user:${LOCAL_SSH_PASSWORD}" | chpasswd && \
    echo "ssh-user:${LOCAL_SSH_PASSWORD}" && \
    echo 'cd /var/www/html' >> /home/ssh-user/.bash_profile

RUN mkdir -p /home/ssh-user/.ssh && \
    chown -R ssh-user:ssh-user /home/ssh-user/.ssh && \
    chmod 700 /home/ssh-user/.ssh

EXPOSE 22

CMD ["/usr/sbin/sshd", "-D", "-e"]

###


## target: production
FROM base-fpm AS build-fpm-composer

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# non-root
USER 82

COPY ./composer.json ./composer.lock /var/www/html/
RUN composer install --no-dev --no-autoloader

RUN composer dump-autoload -o

ARG regex_files='\(htm\|html\|js\|css\|png\|jpg\|jpeg\|gif\|ico\|svg\|webmanifest\)'
ARG regex_path='\(app\/themes\/justice\/error\-pages\|app\/mu\-plugins\|app\/plugins\|wp\)'
RUN mkdir -p ./vendor-assets && \
    find public/ -regex "public\/${regex_path}.*\.${regex_files}" -exec cp --parent "{}" vendor-assets/  \;


###


FROM node:20 AS assets-build

WORKDIR /node
COPY ./public/app/themes/justice/src               ./src
COPY ./public/app/themes/justice/style.css         ./style.css
COPY ./public/app/themes/justice/jsconfig.json     ./jsconfig.json
COPY ./public/app/themes/justice/package.json      ./package.json
COPY ./public/app/themes/justice/package-lock.json ./package-lock.json
COPY ./public/app/themes/justice/webpack.mix.js    ./webpack.mix.js

RUN npm ci
RUN npm run production
RUN rm -rf node_modules


###


FROM base-fpm AS build-fpm

WORKDIR /var/www/html
COPY --chown=www-data:www-data ./config ./config
COPY --chown=www-data:www-data ./public ./public
COPY --from=build-fpm-composer --chown=www-data:www-data /var/www/html/public/wp /var/www/html/public/wp
COPY --from=build-fpm-composer --chown=www-data:www-data /var/www/html/vendor /var/www/html/vendor
COPY --from=assets-build       --chown=www-data:www-data /node/dist/php ./public/app/themes/justice/dist/php

# non-root
USER 82


###


FROM base-nginx AS nginx-dev

RUN echo "# This is a placeholder, because the file is included in `php-fpm.conf`." > /etc/nginx/server_name.conf

###


FROM base-nginx AS build-nginx

# Grab server configurations
COPY deploy/config/php-fpm.conf /etc/nginx/php-fpm.conf
COPY deploy/config/server.conf /etc/nginx/conf.d/default.conf

# WordPress view bootstrapper
COPY public/index.php /var/www/html/public/index.php

# Grab assets for Nginx
COPY --from=assets-build /node/style.css /var/www/html/public/app/themes/justice/
COPY --from=assets-build /node/dist      /var/www/html/public/app/themes/justice/dist/

# Only take what Nginx needs (current configuration)
COPY --from=build-fpm-composer --chown=www-data:www-data /var/www/html/vendor-assets /var/www/html/
COPY --from=build-fpm-composer --chown=www-data:www-data /var/www/html/public/wp/wp-admin/index.php /var/www/html/public/wp/wp-admin/index.php
