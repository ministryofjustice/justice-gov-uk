#!/usr/bin/env bash

# This script creates a JWT secret and saves it into .env.
# If the secrets already exist in the .env file, the script will not overwrite them.

echo "Key Generation: detection..."
source bin/local-key-gen-functions.sh

[[ "$(env_var_exists JWT_SECRET)" == "0" ]] && make_secret JWT

if [[ "$(action_track)" == "0" ]]; then
  echo "Key Generation: no new keys were created."
  clean_up quiet
  exit 0
fi

# Append secrets to the .env file
cat "$FILE_OUTPUT" >> "$ENV_FILE"
echo "Key Generation: new keys were created."

# Clear the variables.
clean_up
