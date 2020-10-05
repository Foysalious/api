#!/usr/bin/env bash

compose_folder="./docker/composes"

compose="docker-compose --env-file=./.env -f $compose_folder/docker-compose.yml"

if [[ $1 = "prod" ]]; then
    extra="-f $compose_folder/docker-compose.prod.yml"
else
    extra="-f $compose_folder/docker-compose.dev.yml"
fi

compose="$compose $extra up"

all_args=($@)
len=${#all_args[@]}
other_args=${all_args[@]:1:${len}-1}
compose="$compose $other_args"

eval "${compose}"
