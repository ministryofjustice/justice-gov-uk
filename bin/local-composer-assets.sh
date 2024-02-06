#!/usr/bin/env ash

# Nginx file sharing regex-parts
regex_files='\(htm\|html\|js\|css\|png\|jpg\|jpeg\|gif\|ico\)'
regex_path='\(app\/themes\/justice\|app\/mu\-plugins\|app\/plugins\|wp\)'

composer install

[ -d "./vendor-assets" ] || mkdir -p ./vendor-assets
find public/ -name '*node_modules*' -prune -name '*justice/src*' -prune -name '*justice/webpack*' -prune -o -type f -regex "public\/${regex_path}.*\.${regex_files}" -exec cp --parent "{}" vendor-assets/  \;

