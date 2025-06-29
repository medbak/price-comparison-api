#!/bin/sh
set -e

while ! mysqladmin ping -h"mysql" -u"app" -p"app" --silent; do
    sleep 1
done

exec "$@"