#!/bin/bash

. ./bin/parse_env.sh

# shellcheck disable=SC2124
test_run_script="docker exec ${CONTAINER_NAME} php artisan test --parallel --testsuite=Feature"
eval "${test_run_script}"
