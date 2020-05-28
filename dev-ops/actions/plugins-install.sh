#!/usr/bin/env bash
__DIR__="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
source $__DIR__/../config.sh

cd $BASE_PATH

bin/console plugin:refresh --no-interaction
bin/console plugin:install --no-interaction $CUSTOM_PLUGINS
bin/console plugin:update --no-interaction $CUSTOM_PLUGINS
bin/console plugin:activate --no-interaction $CUSTOM_PLUGINS
bin/console cache:clear
