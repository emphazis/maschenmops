#!/usr/bin/env bash

__DIR__="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
cd $__DIR__

source ../config.sh

source $DEV_OPS_ACTIONS_PATH/install-before.sh
source $DEV_OPS_ACTIONS_PATH/install-main.sh
source $DEV_OPS_ACTIONS_PATH/install-after.sh
