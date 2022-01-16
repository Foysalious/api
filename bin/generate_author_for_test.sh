#!/bin/bash

. ./bin/parse_env.sh

# shellcheck disable=SC2124
test_run_script="docker exec ${CONTAINER_NAME} php artisan sheba:generate-test-author-list"
eval "${test_run_script}"
