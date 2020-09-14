#!/usr/bin/env bash
CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
source "$CWD/../config.sh"

bin/console plugin:refresh --no-interaction
bin/console plugin:install --no-interaction $CUSTOM_PLUGINS
bin/console plugin:update --no-interaction $CUSTOM_PLUGINS
bin/console plugin:activate --no-interaction $CUSTOM_PLUGINS
bin/console cache:clear
