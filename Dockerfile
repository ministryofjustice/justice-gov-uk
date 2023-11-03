FROM ministryofjustice/intranet-base:latest

ADD . /bedrock
WORKDIR /bedrock

ARG COMPOSER_USER
ARG COMPOSER_PASS
ARG WP_ENV

# Add custom nginx config and init script
RUN mv docker/conf/nginx/server.conf /etc/nginx/sites-available/

# Set execute bit permissions before running build scripts
RUN chmod +x bin/* && sleep 1 && \
    bin/composer-auth.sh && \
    bin/build.sh development && \
    rm -f auth.json
