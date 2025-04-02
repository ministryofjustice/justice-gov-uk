#!/usr/bin/env sh

##
# CONFIGURE COMPOSER AUTHENTICATION
#
# This script will create an `auth.json` file, which is used by composer for
# HTTP basic auth access to the private composer repository www.relevanssi.com.
#
# It requires the environment variables `RELEVANSSI_API_KEY` to be set with 
# authentication credentials.
##

if [ -n "$COMPOSER_TOKEN" ]; then
  composer config --global github-oauth.github.com "$COMPOSER_TOKEN"
fi

if [ -n "$RELEVANSSI_API_KEY" ]
then
  rm -f auth.json
	cat <<- EOF >> auth.json
		{
			"http-basic": {
				"www.relevanssi.com": {
					"username": "",
					"password": "$RELEVANSSI_API_KEY"
				}
			}
		}
	EOF
else
	echo "FATAL: RELEVANSSI_API_KEY was not available."
fi

## check for auth.json
if [ ! -f "auth.json" ]; then
  echo "FATAL: auth.json was not written to the FS."
fi
