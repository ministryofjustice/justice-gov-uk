#!/usr/bin/env bash

if [ ! -f "./vendor-assets/copy-completed" ]; then
  docker compose cp php-fpm:/var/www/html/vendor-assets/public ./vendor-assets
  docker compose cp ./vendor-assets/public nginx:/var/www/html
  touch ./vendor-assets/copy-completed
fi
