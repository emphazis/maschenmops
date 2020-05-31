#!/usr/bin/env bash

CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"

# Increase max_user_watches
sudo sed -i 's/\(fs.inotify.max_user_watches\)=\([0-9].*\)/\1=52488/g' /etc/sysctl.conf

source $CWD/../config.sh

# Copy .env.dist to .env
cp -f "${BASE_PATH}/.env.dist" "${BASE_PATH}/.env"
