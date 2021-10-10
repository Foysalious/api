#!/bin/sh

other_args="--no-interaction --ignore-platform-reqs"

get_composer_command_for_development() {
  FILE=composer-dev.json
  if test -f "$FILE"; then
    echo "env COMPOSER=$FILE composer ${args} ${other_args}"
  else
    echo "composer ${args} ${other_args}"
  fi
}

get_composer_command_for_production() {
  echo "composer ${args} ${other_args}"
}

all_args="$@"

#only the last word
composer_version="${all_args##* }"

#all but the last word
args="${all_args% *}"

if [ "${composer_version}" = "prod" ]; then
  compose="$(get_composer_command_for_production)"
elif [ "${composer_version}" = "dev" ]; then
  compose="$(get_composer_command_for_development)"
fi

eval "${compose}"

