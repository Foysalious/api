#!/bin/sh

get_git_branch() {
  echo "$(git symbolic-ref --short -q HEAD 2>/dev/null)"
}

get_composer_command_for_development() {
  FILE=composer-dev.json
  if test -f "$FILE"; then
    echo "sudo env COMPOSER=$FILE composer ${action} --no-interaction --ignore-platform-reqs"
  else
    echo "sudo composer ${action} --no-interaction --ignore-platform-reqs"
  fi
}

get_composer_command_for_production() {
  echo "sudo composer ${action} --no-interaction --ignore-platform-reqs"
}

if [ $1 = "install" ]; then
  action="install"
elif [ $1 = "update" ]; then
  action="update"
else
  return
fi

branch=$2
if [ -z "${branch}" ]; then
  branch="$(get_git_branch)"
fi

if [ "${branch}" = "master" ]; then
  composer_command="$(get_composer_command_for_production)"
elif [ "${branch}" = "release" ]; then
  composer_command="$(get_composer_command_for_production)"
else
  composer_command="$(get_composer_command_for_development)"
fi

eval "${composer_command}"

