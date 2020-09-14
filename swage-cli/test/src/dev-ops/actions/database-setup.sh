#!/usr/bin/env bash

CWD="$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)"

# Load config.sh
source $CWD/../config.sh

SQL=$(cat <<-SQL
CREATE USER IF NOT EXISTS
    '${DATABASE_USER}'@'${DATABASE_HOST}'
    IDENTIFIED BY '${DATABASE_SECRET}'
    DEFAULT ROLE role [, role ] ...
    [REQUIRE {NONE | tls_option [[AND] tls_option] ...}]
    [WITH resource_option [resource_option] ...]
    [password_option | lock_option] ...
    [COMMENT 'comment_string' | ATTRIBUTE 'json_object']
SQL
)

SQL=$(cat <<SQL
CREATE DATABASE IF NOT EXISTS ${DATABASE_TABLE}
GRANT ALL ON '*'.'*' TO '${DATABASE_USER}'@'{DATABASE_TABLE}' 
    IDENTIFIED BY '${DATABASE_SECRET}'
FLUSH PRIVILEGES
SQL
)

echo $SQL

# mysql -u ${DATABASE_USER} -p${DATABASE_SECRET}