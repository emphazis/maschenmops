#!/usr/bin/env bash

__DIR__="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
source $__DIR__/../config.sh

cp "${DEV_OPS_TEMPLATES_PATH}/.bash_aliases" ~

sed -i "s/__FOLDER_NAME__/${BASE_FOLDER_NAME}/" ~/.bash_aliases

source ~/.bashrc # source .bashrc againg in order for aliases to work
