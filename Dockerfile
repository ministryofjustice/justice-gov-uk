FROM ministryofjustice/wordpress-base-fpm:latest AS base-fpm

###

FROM nginx:1.26.0-alpine as nginx-module-builder

SHELL ["/bin/ash", "-exo", "pipefail", "-c"]

RUN apk update \
    && apk add linux-headers openssl-dev pcre2-dev zlib-dev openssl abuild \
               musl-dev libxslt libxml2-utils make mercurial gcc unzip git \
               xz g++ coreutils \
    # allow abuild as a root user \
    && printf "#!/bin/sh\\nSETFATTR=true /usr/bin/abuild -F \"\$@\"\\n" > /usr/local/bin/abuild \
    && chmod +x /usr/local/bin/abuild \
    && hg clone -r ${NGINX_VERSION}-${PKG_RELEASE} https://hg.nginx.org/pkg-oss/ \
    && cd pkg-oss \
    && mkdir /tmp/packages && \
    /pkg-oss/build_module.sh -v $NGINX_VERSION -f -y -o /tmp/packages -n cachepurge https://github.com/nginx-modules/ngx_cache_purge/archive/2.5.3.tar.gz; \
    BUILT_MODULES="$BUILT_MODULES $(echo cachepurge | tr '[A-Z]' '[a-z]' | tr -d '[/_\-\.\t ]')"; \
    echo "BUILT_MODULES=\"$BUILT_MODULES\"" > /tmp/packages/modules.env

###

FROM nginxinc/nginx-unprivileged:1.26-alpine AS base-nginx

USER root

RUN --mount=type=bind,target=/tmp/packages/,source=/tmp/packages/,from=nginx-module-builder \
    . /tmp/packages/modules.env \
    &&  apk add --no-cache --allow-untrusted /tmp/packages/nginx-module-cachepurge-${NGINX_VERSION}*.apk;

RUN mkdir /var/run/nginx-cache && \
    chown 82:82 /var/run/nginx-cache

# contains gzip and module include
COPY --chown=www-data:www-data deploy/config/nginx.conf /etc/nginx/nginx.conf

COPY deploy/config/init/* /docker-entrypoint.d/
RUN chmod +x /docker-entrypoint.d/*
RUN echo "# This file is configured at runtime." > /etc/nginx/real_ip.conf

USER 82

###

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

ARG COMPOSER_USER
ARG COMPOSER_PASS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY ./bin/composer-auth.sh ./bin/composer-post-install.sh ./bin/

RUN chmod +x ./bin/composer-auth.sh && \
    ./bin/composer-auth.sh
RUN chmod +x ./bin/composer-post-install.sh

# non-root
USER 82

COPY ./composer.json ./composer.lock /var/www/html/
RUN composer install --no-dev

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

# Replace paths with dependencies from build-fpm-composer
ARG path="/var/www/html"
COPY --from=build-fpm-composer ${path}/public/app/mu-plugins public/app/mu-plugins
COPY --from=build-fpm-composer ${path}/public/app/plugins public/app/plugins
COPY --from=build-fpm-composer ${path}/public/app/languages public/app/languages
COPY --from=build-fpm-composer ${path}/public/wp public/wp
COPY --from=build-fpm-composer ${path}/vendor vendor
COPY --from=assets-build       --chown=www-data:www-data /node/dist/php public/app/themes/justice/dist/php

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

WORKDIR /var/www/html

# WordPress view bootstrapper
COPY public/index.php                      public/index.php
COPY public/app/themes/justice/error-pages public/app/themes/justice/error-pages/


# Only take what Nginx needs (current configuration)
COPY --from=build-fpm-composer --chown=www-data:www-data /var/www/html/public/wp/wp-admin/index.php public/wp/wp-admin/index.php
COPY --from=build-fpm-composer --chown=www-data:www-data /var/www/html/vendor-assets                ./

# Grab assets for Nginx
COPY --from=assets-build /node/dist        public/app/themes/justice/dist/
COPY --from=assets-build /node/style.css   public/app/themes/justice/
