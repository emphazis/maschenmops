#!/usr/bin/env bash

CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"

# Install Plugins
"$CWD/plugins-register.sh" && "$CWD/plugins-install.sh"
