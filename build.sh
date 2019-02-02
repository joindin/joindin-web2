#!/bin/bash
set -e

parallel-lint --exclude vendor .

phpcs \
    --standard=psr2 \
    --ignore=vendor \
    --extensions=php \

    --runtime-set ignore_warnings_on_exit true \
    -p \
    .

cd tests
phpunit
