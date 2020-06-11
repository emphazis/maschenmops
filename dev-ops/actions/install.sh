#!/usr/bin/env bash

CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"

"$CWD/install-before.sh" && "$CWD/install-main.sh" && "$CWD/install-after.sh"
