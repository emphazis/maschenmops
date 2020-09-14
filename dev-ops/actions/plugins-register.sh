#!/usr/bin/env bash
CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"
source "$CWD/../config.sh"

git submodule sync --recursive
git submodule update --init --recursive

cd $CUSTOM_PATH/static-plugins

for dir in */    # list directories in the form "/tmp/dirname/"
do
    dir2=${dir%*/}      # remove the trailing "/"
    PLUGIN_FOLDER_NAME=${dir2##*/}    # print everything after the final "/"

    PATH_TO_STATIC_PLUGIN="${CUSTOM_PATH}/static-plugins/${PLUGIN_FOLDER_NAME}"
    PATH_TO_LINKED_PLUGIN="${CUSTOM_PATH}/plugins/${PLUGIN_FOLDER_NAME}"

    if [ ! -d $PATH_TO_STATIC_PLUGIN ]; then
        echo "Plugin not found: $PATH_TO_STATIC_PLUGIN"
        continue
    fi

    cd $PATH_TO_STATIC_PLUGIN

    git fetch origin master
    composer install --no-interaction --no-scripts --optimize-autoloader --no-suggest

    if [ ! -L $PATH_TO_LINKED_PLUGIN ]; then
        ln -s $PATH_TO_STATIC_PLUGIN $PATH_TO_LINKED_PLUGIN
    fi

done

cd $CWD
