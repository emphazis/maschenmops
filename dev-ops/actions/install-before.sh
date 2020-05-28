#!/usr/bin/env bash

__DIR__="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
cd $__DIR__

echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf && sudo sysctl -p

source ../config.sh

cd $BASE_PATH
cp -f "./.env.dist" "./.env"
