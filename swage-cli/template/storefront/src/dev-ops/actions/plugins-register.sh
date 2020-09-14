#!/usr/bin/env bash
CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"

CUSTOM_PATH="$CWD/../../custom"

git submodule sync --recursive
git submodule update --init --recursive

for dir in "$CWD/../../static-plugins/*/"     # list directories in the form "/tmp/dirname/"
do
    dir=${dir%*/}      # remove the trailing "/"
    PLUGIN_FOLDER_NAME=${dir##*/}    # print everything after the final "/"

    cd "${dir}/static-plugins/${PLUGIN_FOLDER_NAME}" 

    git fetch origin master
    composer install --no-interaction --optimize-autoloader --no-suggest

    if [ ! -L "${CUSTOM_PATH}/plugins/${PLUGIN_FOLDER_NAME}" ]; then
        ln -s "${CUSTOM_PATH}/static-plugins/${PLUGIN_FOLDER_NAME}" "${CUSTOM_PATH}/plugins/${PLUGIN_FOLDER_NAME}"
    fi

done

cd $CWD
