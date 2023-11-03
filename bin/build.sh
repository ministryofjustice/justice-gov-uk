#!/bin/bash
set -e

printenv

# Build Script
# Use this script to build theme assets,
# and perform any other build-time tasks.

# Install PHP dependencies (WordPress, plugins, etc.)
if [[ "$1" == "production" ]]
then
  composer install --no-dev
else
  composer install
fi

# Build theme assets
cd web/app/themes/justice
npm install
npm run "$1"
rm -rf node_modules
cd ../../../..
