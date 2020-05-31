#!/usr/bin/env bash

__DIR__="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
cd $__DIR__

source ../config.sh

# Install Plugins
source $DEV_OPS_ACTIONS_PATH/plugins-register.sh
source $DEV_OPS_ACTIONS_PATH/plugins-install.sh
