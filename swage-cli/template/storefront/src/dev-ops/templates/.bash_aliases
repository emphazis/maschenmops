#!/usr/bin/env bash

alias cache="~/__FOLDER_NAME__/bin/console cache:clear"

alias install="~/__FOLDER_NAME__/dev-ops/actions/install.sh"

alias static-plugins="~/__FOLDER_NAME__/dev-ops/actions/plugins-register.sh \
                      && ~/__FOLDER_NAME__/dev-ops/actions/plugins-install.sh"

alias watch="bash --rcfile <(echo '. ~/.bashrc; ~/__FOLDER_NAME__/bin/watch-storefront.sh')"

alias watch-administration="bash --rcfile <(echo '. ~/.bashrc; ~/__FOLDER_NAME__/bin/watch-administration.sh')"
