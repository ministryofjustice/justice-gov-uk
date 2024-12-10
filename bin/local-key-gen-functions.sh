#!/usr/bin/env bash

ACTION_TRACKER="/tmp/justice_action_tracker"

ENV_FILE=".env"
FILE_OUTPUT="/tmp/justice_secrets_string"

# Create outputs files
touch $FILE_OUTPUT
{
  echo -e "\n# # # # # # # # # # # # # # # # # #"
  echo "# -->  auto-gen secrets keys  <-- #"
  echo "# # # # # # # # # # # # # # # # # #"
} > $FILE_OUTPUT

env_var_exists(){
  VAR=$(< "$ENV_FILE" grep -w "$1")
  VALUE=${VAR#*=}
  VALUE_SIZE=${#VALUE}

  if [[ $VALUE_SIZE -gt 25 ]] ; then
      echo "$1 exists with a value"
      echo "$VALUE"
  else
      echo "0"
  fi
}

touch $ACTION_TRACKER
action_track(){
  TRACKER_SIZE=$(sed -n '$='  "$ACTION_TRACKER")
  if [[ "$TRACKER_SIZE" -gt 0 ]] ; then
      echo "1"
  else
      echo "0"
  fi
}

make_secret(){
  case $1 in

    JWT)
      echo "Generating JWT secret"
      ## append to file
      echo -e "JWT_SECRET=$(openssl rand -base64 128 | tr -d '\n')\n" >> "$FILE_OUTPUT"
      echo "JWT created" >> "$ACTION_TRACKER"
      ;;

  esac
}

clean_up(){
  [[ -f "$ACTION_TRACKER" ]] && rm "$ACTION_TRACKER"
  [[ -f "$FILE_OUTPUT" ]] && rm "$FILE_OUTPUT"
}
