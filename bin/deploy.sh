#!/bin/sh

get_git_branch() {
    echo "$(git symbolic-ref --short -q HEAD 2>/dev/null)"
}

pull_from_git() {
    git fetch origin
    reset="sudo git reset --hard origin/"
    reset_branch="$reset$1"
    eval ${reset_branch}

    ./bin/dcup.sh dev -d
}

pull_from_docker_registry() {
    . ./bin/parse_env.sh
    docker pull registry.sheba.xyz/${CONTAINER_NAME}

    ./bin/dcup.sh prod -d
}

branch=$1
if [ -z ${branch} ]; then
    branch="$(get_git_branch)"
fi

if [ ${branch} = "development" ]; then
    pull_from_git ${branch}
elif [ ${branch} = "master" ]; then
    pull_from_docker_registry
fi
