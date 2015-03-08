set -x
if [ "$TRAVIS_PHP_VERSION" = '5.6' ] ; then
    ./vendor/bin/test-reporter --stdout > codeclimate.json
    curl -X POST -d @codeclimate.json -H "Content-Type: application/json" -H "User-Agent: Code Climate (PHP Test Reporter v1.0.1-dev)"  https://codeclimate.com/test_reports
    php vendor/bin/coveralls
fi
