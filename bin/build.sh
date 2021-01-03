#!/usr/bin/env bash
. ./bin/parse_env.sh

docker build --no-cache -t "${CONTAINER_NAME}" -f ./docker/Dockerfile.production . --build-arg APP_ENV="${APP_ENV}"
docker tag "${CONTAINER_NAME}":latest registry.sheba.xyz/"${CONTAINER_NAME}":latest
docker push registry.sheba.xyz/"${CONTAINER_NAME}":latest
