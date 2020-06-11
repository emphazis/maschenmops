#!/usr/bin/env bash

CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"

# Increase max_user_watches
sudo sed -i 's/\(fs.inotify.max_user_watches\)=\([0-9].*\)/\1=52488/g' /etc/sysctl.conf

cd "$CWD/../.."

# Copy .env.dist to .env
# cp -f "./.env.dist" "./.env"

# Composer install
composer install -o --prefer-dist --no-dev
