#!/usr/bin/env bash

REL_PATH_TO_BASE_FOLDER=".."
BASE_PATH="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")"/${REL_PATH_TO_BASE_FOLDER} && pwd -P)"

export PROJECT_ROOT="${PROJECT_ROOT:-"$(dirname $BASE_PATH)"}"

export BASE_PATH
export BASE_FOLDER_NAME=${BASE_PATH##*/} # print everything after the final "/"

export DEV_OPS_PATH="${BASE_PATH}/dev-ops"
export DEV_OPS_TEMPLATES_PATH="${DEV_OPS_PATH}/templates"
export DEV_OPS_ACTIONS_PATH="${DEV_OPS_PATH}/actions"

export CUSTOM_PATH="${BASE_PATH}/custom"

export CUSTOM_PLUGINS="DigitalStore MaschenmopsTheme"

export $(egrep -v '^#' "${BASE_PATH}/.env" | xargs)
