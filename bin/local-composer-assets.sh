#!/usr/bin/env ash

if [ ! -d "./vendor" ]; then
  composer install
fi
