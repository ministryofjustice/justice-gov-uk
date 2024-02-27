FROM php:8.2-fpm-alpine AS base-fpm

RUN apk add --update bash  \
    zlib-dev  \
    libpng-dev  \
    libzip-dev  \
    libxml2-dev \
    ghostscript imagemagick imagemagick-libs imagemagick-dev libjpeg-turbo libgomp freetype-dev \
    icu-dev  \
    htop  \
    mariadb-client \
    # For debugging only. TODO: remove aws-cli in production.
    aws-cli \ 
    $PHPIZE_DEPS

RUN pecl install imagick
RUN docker-php-ext-enable imagick && \
    docker-php-ext-configure intl && \
    docker-php-ext-install exif gd zip mysqli opcache intl
RUN apk del $PHPIZE_DEPS

RUN echo "opcache.jit_buffer_size=500000000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Install wp-cli
RUN curl -o /usr/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x /usr/bin/wp

# Start temporary debugging

# Install git
RUN apk add --update git

# Install go
COPY --from=golang:1.20-alpine /usr/local/go/ /usr/local/go/
ENV GOPATH /go
ENV PATH $GOPATH/bin:$PATH 
RUN mkdir -p "$GOPATH/src" "$GOPATH/bin" && chmod -R 777 "$GOPATH"
ENV PATH="/usr/local/go/bin:${PATH}"

# Build gost from source
RUN cd /go/src && git clone https://github.com/ginuerzh/gost.git
RUN cd /go/src/gost/cmd/gost && go mod tidy -e && go build
RUN cp /go/src/gost/cmd/gost/gost /go/bin

# Now we can access this podvia kubectl exec
# Start a tunnel like e.g. `gost -L=tcp://:8888/google.com:80`
# In a sererate terminal do port frowarding to that pod `kubectl port-forward pods/php-fpm-xyx 8888:8888`
# On the local machine, we should be able to access google.com via the pod at `localhost:8888`

# We can apply this same principal to access the AWS meta API.
# It's a fixed url accessible via EC@/pods only at ip address 169.254.170.2
# As seen in public/app/plugins/amazon-s3-and-cloudfront/vendor/Aws3/Aws/Credentials/EcsCredentialProvider.php
# Use the gost command:  `gost -L=tcp://:8888/169.254.170.2:80`
# Ensure `kubectl port-forward pods/php-fpm-xyx 8888:8888` is running in a seperate terminal.
# On the local machine, we should be able to access the AWS API at localhost:8888
# Temporarily update the hardcoded url at public/app/plugins/amazon-s3-and-cloudfront/vendor/Aws3/Aws/Credentials/EcsCredentialProvider.php 
#   from 169.254.170.2 to host.docker.internal
# Run the application via docker compose with use-server-roles set to true.
# Now we should be able to debug and explore with a local codebase, connecting to and using live server-role credentials.

# End temporary debugging


###


FROM nginxinc/nginx-unprivileged:1.25-alpine AS base-nginx

USER root

COPY deploy/config/init/* /docker-entrypoint.d/
RUN chmod +x /docker-entrypoint.d/*
RUN echo "# This file is configured at runtime." > /etc/nginx/real_ip.conf

USER 82


## target: dev
FROM base-fpm AS dev
RUN apk add --update nano nodejs npm

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

COPY ./composer.json /var/www/html/composer.json
RUN composer install --no-dev --no-scripts --no-autoloader

COPY . .
RUN composer install --no-dev
RUN composer dump-autoload -o

ARG regex_files='\(htm\|html\|js\|css\|png\|jpg\|jpeg\|gif\|ico\|svg\|webmanifest\)'
ARG regex_path='\(app\/themes\/justice\/error\-pages\|app\/mu\-plugins\|app\/plugins\|wp\)'
RUN mkdir -p ./vendor-assets && \
    find public/ -regex "public\/${regex_path}.*\.${regex_files}" -exec cp --parent "{}" vendor-assets/  \;



###


FROM base-fpm AS build-fpm

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
RUN npm ci
RUN npm run production
RUN rm -rf node_modules


###


FROM base-nginx AS nginx-dev

RUN echo "# This is a placeholder, because the file is included in `php-fpm.conf`." > /etc/nginx/server_name.conf

###


FROM base-nginx AS build-nginx

# Grab server configurations
COPY deploy/config/php-fpm.conf /etc/nginx/php-fpm.conf
COPY deploy/config/server.conf /etc/nginx/conf.d/default.conf

# Grab assets for Nginx
COPY --from=assets-build /code/public/app/themes/justice/style.css /var/www/html/public/app/themes/justice/
COPY --from=assets-build /code/public/app/themes/justice/dist /var/www/html/public/app/themes/justice/dist/

# Only take what Nginx needs (current configuration)
COPY --from=build-fpm-composer --chown=www-data:www-data /var/www/html/vendor-assets /var/www/html/
COPY --from=build-fpm-composer --chown=www-data:www-data /var/www/html/public/index.php /var/www/html/public/index.php
COPY --from=build-fpm-composer --chown=www-data:www-data /var/www/html/public/wp/wp-admin/index.php /var/www/html/public/wp/wp-admin/index.php
