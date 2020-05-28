#!/usr/bin/env bash
__DIR__="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
cd $__DIR__

source $__DIR__/../config.sh

cd $BASE_PATH

bin/console plugin:uninstall --no-interaction $CUSTOM_PLUGINS
bin/console plugin:refresh
bin/console cache:clear
