#!/usr/bin/env bash

. ./bin/parse_env.sh

docker build -t ${CONTAINER_NAME} -f ./docker/Dockerfile .
docker tag ${CONTAINER_NAME}:latest registry.sheba.xyz/${CONTAINER_NAME}:latest
docker push registry.sheba.xyz/${CONTAINER_NAME}:latest
