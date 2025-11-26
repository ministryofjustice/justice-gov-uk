ARG version_nginx=1.28.0

FROM ministryofjustice/wordpress-base-fpm:latest AS base-fpm

# Switch to the alpine's default user, for installing packages
USER root

# Add hunspell for spell checking
RUN apk update --no-cache \
    && apk add hunspell hunspell-en-gb

# Make the Nginx user available in this container
RUN addgroup -g 101 -S nginx; adduser -u 101 -S -D -G nginx nginx

RUN mkdir /sock && \
    chown nginx:nginx /sock

# Copy our healthcheck scripts and set them to executable
COPY bin/fpm-liveness.sh bin/fpm-readiness.sh bin/fpm-status.sh /usr/local/bin/fpm-health/

RUN chmod +x /usr/local/bin/fpm-health/*

## Change directory
WORKDIR /usr/local/etc/php-fpm.d

## Clean PHP pools; leave docker.conf in situe
RUN rm zz-docker.conf && \
    rm www.conf.default && \
    rm www.conf

## Set our pool configuration
COPY deploy/config/php-pool.conf pool.conf

# Disable access logging in PHP-FPM to reduce log noise.
RUN sed -i 's/^[[:space:]]*access\.log/;access.log/' /usr/local/etc/php-fpm.d/docker.conf

# Create volumes for so that the directories are writeable when the container is in read-only mode.
VOLUME /tmp /var/www/html/public/app/uploads

WORKDIR /var/www/html

# Set IMAGE_TAG at build time, we don't want this container to be run with an incorrect IMAGE_TAG.
# Set towards the end of the Dockerfile to benefit from caching.
ARG IMAGE_TAG
ENV IMAGE_TAG=$IMAGE_TAG


###

FROM nginx:${version_nginx}-alpine AS nginx-module-builder

SHELL ["/bin/ash", "-exo", "pipefail", "-c"]

RUN apk update \
    && apk add linux-headers openssl-dev pcre2-dev zlib-dev openssl abuild \
               musl-dev libxslt libxml2-utils make gcc unzip git \
               xz g++ coreutils \
    # allow abuild as a root user \
    && printf "#!/bin/sh\\nSETFATTR=true /usr/bin/abuild -F \"\$@\"\\n" > /usr/local/bin/abuild \
    && chmod +x /usr/local/bin/abuild \
    && git clone --branch ${NGINX_VERSION}-${PKG_RELEASE} https://github.com/nginx/pkg-oss.git pkg-oss \
    && cd pkg-oss \
    && mkdir /tmp/packages && \
    /pkg-oss/build_module.sh -v $NGINX_VERSION -f -y -o /tmp/packages -n cachepurge https://github.com/nginx-modules/ngx_cache_purge/archive/2.5.3.tar.gz; \
    BUILT_MODULES="$BUILT_MODULES $(echo cachepurge | tr '[A-Z]' '[a-z]' | tr -d '[/_\-\.\t ]')"; \
    echo "BUILT_MODULES=\"$BUILT_MODULES\"" > /tmp/packages/modules.env


###

FROM nginxinc/nginx-unprivileged:${version_nginx}-alpine AS base-nginx

USER root

RUN --mount=type=bind,target=/tmp/packages/,source=/tmp/packages/,from=nginx-module-builder \
    . /tmp/packages/modules.env \
    &&  apk add --no-cache --allow-untrusted /tmp/packages/nginx-module-cachepurge-${NGINX_VERSION}*.apk;

RUN mkdir /var/run/nginx-cache && \
    chown nginx:nginx /var/run/nginx-cache

# contains gzip and module include
COPY --chown=nginx:nginx deploy/config/nginx.conf /etc/nginx/nginx.conf

COPY deploy/config/init/configure-real-ip.sh /docker-entrypoint.d/
RUN chmod +x /docker-entrypoint.d/*
RUN echo "# This file is configured at runtime." > /etc/nginx/real_ip.conf

USER 101

###

## target: dev
FROM base-fpm AS dev
RUN apk add --update nano nodejs npm inotify-tools

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Add MariaDB client config to disable SSL by default
# Without this, `wp db` commands fail because the local connection is not SSL-enabled
RUN echo -e "[client]\nssl=0" > /home/nginx/.my.cnf \
    && chown nginx:nginx /home/nginx/.my.cnf

VOLUME ["/sock"]
# nginx
USER 101



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

ARG RELEVANSSI_API_KEY

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY ./bin/composer-auth.sh ./bin/composer-post-install.sh ./bin/

RUN chmod +x ./bin/composer-auth.sh && \
    ./bin/composer-auth.sh
RUN chmod +x ./bin/composer-post-install.sh

# non-root
USER 101

COPY ./composer.json ./composer.lock /var/www/html/
RUN composer install --no-dev

RUN composer dump-autoload -o

ARG regex_files='\(js\|css\|png\|jpg\|jpeg\|gif\|ico\|svg\|webmanifest\)'
ARG regex_path='\(app\/mu\-plugins\|app\/plugins\|wp\)'

RUN mkdir -p ./vendor-assets && \
    # Copy the theme's error pages
    find public/ -regex "public\/app\/themes\/justice\/error\-pages\.*\.html" -exec cp --parent "{}" vendor-assets/  \; && \
    # Copy frontend assets from mu-plugins, plugins and wp.
    find public/ -regex "public\/${regex_path}.*\.${regex_files}" -exec cp --parent "{}" vendor-assets/  \;


###


FROM node:24 AS assets-build

WORKDIR /node
COPY ./public/app/themes/justice/src               ./src
COPY ./public/app/themes/justice/.npmrc            ./.npmrc
COPY ./public/app/themes/justice/style.css         ./style.css
COPY ./public/app/themes/justice/jsconfig.json     ./jsconfig.json
COPY ./public/app/themes/justice/package.json      ./package.json
COPY ./public/app/themes/justice/package-lock.json ./package-lock.json
COPY ./public/app/themes/justice/webpack.mix.js    ./webpack.mix.js

RUN npm ci
RUN npm run production
RUN rm -rf node_modules


###

FROM ruby:3 AS jekyll-dev

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    git \
    && rm -rf /var/lib/apt/lists/*

# Update the Ruby bundler and install Jekyll
RUN gem update bundler && gem install bundler jekyll

WORKDIR /home

###

FROM base-fpm AS build-fpm

WORKDIR /var/www/html
COPY --chown=nginx:nginx ./config ./config
COPY --chown=nginx:nginx ./public ./public
COPY --chown=nginx:nginx wp-cli.yml wp-cli.yml

# Replace paths with dependencies from build-fpm-composer
ARG path="/var/www/html"
COPY --from=build-fpm-composer ${path}/public/app/mu-plugins public/app/mu-plugins
COPY --from=build-fpm-composer ${path}/public/app/plugins public/app/plugins
COPY --from=build-fpm-composer ${path}/public/app/languages public/app/languages
COPY --from=build-fpm-composer ${path}/public/wp public/wp
COPY --from=build-fpm-composer ${path}/vendor vendor
COPY --from=assets-build       --chown=nginx:nginx /node/dist/php public/app/themes/justice/dist/php

# non-root
USER 101


###


FROM base-nginx AS nginx-dev


###


FROM base-nginx AS build-nginx

# Grab server configurations
COPY deploy/config/php-fpm.conf      /etc/nginx/php-fpm.conf
COPY deploy/config/php-fpm-auth.conf /etc/nginx/php-fpm-auth.conf
COPY deploy/config/auth-request.conf /etc/nginx/auth-request.conf
COPY deploy/config/headers.conf      /etc/nginx/headers.conf
COPY deploy/config/redirects.conf    /etc/nginx/redirects.conf
COPY deploy/config/server.conf       /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html

# WordPress view bootstrapper
COPY public/index.php                         public/index.php
COPY public/app/themes/justice/error-pages    public/app/themes/justice/error-pages/
COPY public/app/themes/justice/screenshot.png public/app/themes/justice/screenshot.png


# Only take what Nginx needs (current configuration)
COPY --from=build-fpm-composer --chown=nginx:nginx /var/www/html/public/wp/wp-admin/index.php public/wp/wp-admin/index.php
COPY --from=build-fpm-composer --chown=nginx:nginx /var/www/html/vendor-assets                ./

# Grab assets for Nginx
COPY --from=assets-build /node/dist        public/app/themes/justice/dist/
COPY --from=assets-build /node/style.css   public/app/themes/justice/


###


FROM alpine:3.22 AS build-s3-push

ARG user=s3pusher
RUN addgroup --gid 3001 ${user} && adduser -D -G ${user} -g "${user} user" -u 3001 ${user}

RUN apk add --no-cache aws-cli jq

WORKDIR /usr/bin

COPY deploy/config/init/s3-push-start.sh ./s3-push-start
RUN chmod +x s3-push-start

USER 3001

# Go home...
WORKDIR /home/s3pusher

# Grab assets for pushing to s3
COPY --from=build-fpm-composer /var/www/html/vendor-assets ./
COPY --from=assets-build /node/dist public/app/themes/justice/dist/

# Set IMAGE_TAG at build time, we don't want this container to be run with an incorrect IMAGE_TAG.
# Set towards the end of the Dockerfile to benefit from caching.
ARG IMAGE_TAG
ENV IMAGE_TAG=$IMAGE_TAG

ENTRYPOINT ["/bin/sh", "-c", "s3-push-start"]
