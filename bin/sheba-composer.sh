#!/bin/sh

other_args="--no-interaction --ignore-platform-reqs"

get_git_branch() {
  echo "$(git symbolic-ref --short -q HEAD 2>/dev/null)"
}

get_composer_command_for_development() {
  FILE=composer-dev.json
  if test -f "$FILE"; then
    echo "sudo env COMPOSER=$FILE composer ${args} ${other_args}"
  else
    echo "sudo composer ${args} ${other_args}"
  fi
}

get_composer_command_for_production() {
  echo "sudo composer ${args} ${other_args}"
}

echo "${package_name}"

all_args="$@"

#only the last word
branch="${all_args##* }"

#all but the last word
args="${all_args% *}"

if [ -z "${branch}" ]; then
  branch="$(get_git_branch)"
fi

if [ "${branch}" = "master" ]; then
  compose="$(get_composer_command_for_production)"
elif [ "${branch}" = "release" ]; then
  compose="$(get_composer_command_for_production)"
else
  compose="$(get_composer_command_for_development)"
fi

eval "${compose}"

