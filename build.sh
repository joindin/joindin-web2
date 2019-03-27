#!/bin/bash
set -e

vendor/bin/parallel-lint --exclude vendor .

vendor/bin/phpcs \
    --standard=psr2 \
    --ignore=vendor \
    --extensions=php \
    --runtime-set ignore_warnings_on_exit true \
    -p \
    .

php vendor/bin/security-checker security:check composer.lock

vendor/bin/phpunit -c ./phpunit.xml.dist
