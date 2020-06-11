#!/usr/bin/env bash

CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"

# Load config.sh
source $CWD/../config.sh

cd /etc/ssl

# Create a Certificate Signing Request (CSR)
cd "/etc/ssl/certs"
if [ ! -f "${}-key.pem" ]; then
    openssl genrsa 2048 > maschenmops-key.pem
fi


openssl genrsa 2048 > ca-key.pem
openssl req -new -x509 -nodes -days 3600 -key ca-key.pem -out ca.pem