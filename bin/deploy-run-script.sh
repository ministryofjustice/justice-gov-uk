#!/usr/bin/env bash

BASIC_AUTH_BASE64=""

## Prevent errors when basic auth isn't used
##
## Nb.the BASIC_AUTH_USER secret in GH production environment should
## be set to `no-basic-auth` if not being used
if [ "$BASIC_AUTH_USER" != "no-basic-auth" ]; then
  BASIC_AUTH_BASE64=$(htpasswd -nbm "$BASIC_AUTH_USER" "$BASIC_AUTH_PASS" | base64 -w 0)
fi

export BASIC_AUTH_BASE64

## Perform find/replace
< "$TPL_PATH"/secret.tpl.yml envsubst > "$TPL_PATH"/secret.yaml
< "$TPL_PATH"/deployment.tpl.yml envsubst > "$TPL_PATH"/deployment.yaml

## Remove template files before apply
rm "$TPL_PATH"/secret.tpl.yml
rm "$TPL_PATH"/deployment.tpl.yml
