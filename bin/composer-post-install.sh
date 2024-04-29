#!/usr/bin/env sh

# Fix a typo in the wp-document-revisions plugin.

# Define the search and replace strings.
WPDR_FILE=public/app/plugins/wp-document-revisions/includes/class-wp-document-revisions.php
WPDR_SEARCH=subdtr
WPDR_REPLACE=subdir

# If serach string is in file. Then replace it.
if grep -q  $WPDR_SEARCH $WPDR_FILE ; then
  echo "Fixing typo in wp-document-revisions..."
  sed -i "s/$WPDR_SEARCH/$WPDR_REPLACE/g" $WPDR_FILE
fi
