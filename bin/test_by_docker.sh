#!/bin/bash

. ./bin/parse_env.sh

get_full_namespace() {
  namespace=$1
  folder=$2
  if [[ ! -z ${folder} ]]; then
    folder=$(echo ${folder} | sed 's/\\/\//g')
    folder="${folder//\//\\\\}"
    namespace="${namespace}\\\\${folder}"
  fi
  echo ${namespace}
}

get_feature_suite() {
  namespace='Tests\\Feature'
  # shellcheck disable=SC2046
  # shellcheck disable=SC2005
  echo $(get_full_namespace ${namespace} $1)
}

get_unit_suite() {
  namespace='Tests\\Unit'
  # shellcheck disable=SC2046
  # shellcheck disable=SC2005
  echo $(get_full_namespace ${namespace} $1)
}

if [[ $1 == "unit" ]]; then
  suite="$(get_unit_suite $2)"
elif [[ $1 == "feature" ]]; then
  suite="$(get_feature_suite $2)"
else
  suite="$1"
fi

# shellcheck disable=SC2124
test_run_script="docker exec ${CONTAINER_NAME} php vendor/bin/phpunit -d memory_limit=6144M"
# shellcheck disable=SC2236
if [ ! -z "$suite" ]; then
  test_run_script+=" --filter ${suite}"
fi
eval "${test_run_script}"
