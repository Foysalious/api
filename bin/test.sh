#!/bin/bash

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
    echo $(get_full_namespace ${namespace} $1)
}

get_unit_suite() {
    namespace='Tests\\Unit'
    echo $(get_full_namespace ${namespace} $1)
}

if [[ $1 = "unit" ]]
then
    suite="$(get_unit_suite $2)"
elif [[ $1 = "feature" ]]
then
    suite="$(get_feature_suite $2)"
else
    suite="$1"
fi

php vendor/bin/phpunit -d memory_limit=4096M --filter "${suite}"
