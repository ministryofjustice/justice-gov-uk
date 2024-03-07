#!/bin/bash

args=("$@")

function run_scripts {
    # Loop over the args
    for script in "${args[@]}"
    do
        # Run the script
        composer $script
    done
}

run_scripts

inotifywait -r -m ./config -m ./public/app -m ./spec -m ./vendor  -e create -e moved_to -e modify --include '.*\.php$' |
    while read -r directory action file; do
        run_scripts
    done
