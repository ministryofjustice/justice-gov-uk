ARG version_nginx=1.26.2

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

# Copy our init. script(s) and set them to executable
COPY deploy/config/init/fpm-*.sh /usr/local/bin/docker-entrypoint.d/

RUN chmod +x /usr/local/bin/docker-entrypoint.d/*

# Copy our healthcheck scripts and set them to executable
COPY bin/fpm-liveness.sh bin/fpm-readiness.sh bin/fpm-status.sh /usr/local/bin/fpm-health/

RUN chmod +x /usr/local/bin/fpm-health/*

# Copy our stop script and set it to executable
COPY bin/fpm-stop.sh /usr/local/bin/fpm-stop.sh

RUN chmod +x /usr/local/bin/fpm-stop.sh

## Change directory
WORKDIR /usr/local/etc/php-fpm.d

## Clean PHP pools; leave docker.conf in situe
RUN rm zz-docker.conf && \
    rm www.conf.default && \
    rm www.conf

## Set our pool configuration
COPY deploy/config/php-pool.conf pool.conf

# Create volumes for so that the directories are writeable when the container is in read-only mode.
VOLUME /tmp /var/www/html/public/app/uploads

WORKDIR /var/www/html


###

FROM nginx:${version_nginx}-alpine AS nginx-module-builder

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

FROM nginxinc/nginx-unprivileged:${version_nginx}-alpine AS base-nginx

USER root

RUN --mount=type=bind,target=/tmp/packages/,source=/tmp/packages/,from=nginx-module-builder \
    . /tmp/packages/modules.env \
    &&  apk add --no-cache --allow-untrusted /tmp/packages/nginx-module-cachepurge-${NGINX_VERSION}*.apk;

RUN mkdir /var/run/nginx-cache && \
    chown nginx:nginx /var/run/nginx-cache

# contains gzip and module include
COPY --chown=nginx:nginx deploy/config/nginx.conf /etc/nginx/nginx.conf

COPY deploy/config/init/* /docker-entrypoint.d/
RUN chmod +x /docker-entrypoint.d/*
RUN echo "# This file is configured at runtime." > /etc/nginx/real_ip.conf

USER 101

###

## target: dev
FROM base-fpm AS dev
RUN apk add --update nano nodejs npm inotify-tools

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

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

# ⛓️‍💥 NPM supply chain attack note: Trust Docker Hub and Deno to provide a safe container image.
FROM denoland/deno AS deno-base

WORKDIR /app

# Set the path for the esbuild binary, accessed in the Dockerfile and build.js
ENV ESBUILD_BINARY_PATH=/usr/local/bin/esbuild

# Install curl and ca-certificates for downloading esbuild
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    curl \
    ca-certificates

# Install, verify and extract esbuild
# The version should match the one used in build.js

# ⛓️‍💥 NPM supply chain attack note: Trust esbuild to have published a safe package (esbuild binary) to npm
RUN curl https://registry.npmjs.org/@esbuild/linux-x64/-/linux-x64-0.25.10.tgz -o /tmp/esbuild-linux-x64.tgz

# Clean up the apt cache to reduce image size
RUN apt-get remove --purge -y curl && \
    apt-get autoremove -y && \
    rm -rf /var/lib/apt/lists/*

# Verify the checksum of the downloaded file, exit if it doesn't match
# The checksum is obtained by downloading the file and running `sha256sum` on it.
# This isn't ideal, but it does identify tampering after the first build.

# ⛓️‍💥 NPM supply chain attack note: Trust that the package was safe, when it was first downloaded to obtain the checksum.
RUN echo '25a7b968b8e5172baaa8f44f91b71c1d2d7e760042c691f22ab59527d870d145 /tmp/esbuild-linux-x64.tgz' | sha256sum -c

# Extract the package and move the esbuild binary to the correct location
RUN tar -xzf /tmp/esbuild-linux-x64.tgz -C /tmp && \
    mv /tmp/package/bin/esbuild $ESBUILD_BINARY_PATH && \
    chmod +x $ESBUILD_BINARY_PATH && \
    rm -rf /tmp/package /tmp/esbuild-linux-x64.tgz

# Set all of /app and /app/dist as writeable by the Deno user.
RUN mkdir -p /app/dist && \
    chown -R deno:deno /app && \
    chown -R deno:deno /app/dist

# Make /app/node_modules and set it writeable by the Deno user.
RUN mkdir -p /app/node_modules && \
    chown -R deno:deno /app/node_modules
    
# Prefer not to run as root.
USER deno

# Cache the dependencies as a layer (the following two steps are re-run only when deps.ts is modified).
COPY ./public/app/themes/justice/deno.jsonc        ./deno.jsonc
COPY ./public/app/themes/justice/deno.lock         ./deno.lock
COPY ./public/app/themes/justice/package.json      ./package.json

# ⛓️‍💥 NPM supply chain attack note: scripts like postinstall are disallowed by default
RUN deno install --frozen

# Create the deno-dev target, it's the same as base, but added convenience.
FROM deno-base AS deno-dev


FROM deno-base AS deno-build

# Copy the rest of the source code.
# This will be re-run only when the source code is modified.
COPY ./public/app/themes/justice/src               ./src
COPY ./public/app/themes/justice/build.js          ./build.js
COPY ./public/app/themes/justice/style.css         ./style.css
COPY ./public/app/themes/justice/jsconfig.json     ./jsconfig.json

RUN deno task build


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
COPY --from=deno-build         --chown=nginx:nginx /app/dist/php public/app/themes/justice/dist/php

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
COPY --from=deno-build /app/dist        public/app/themes/justice/dist/
COPY --from=deno-build /app/style.css   public/app/themes/justice/
