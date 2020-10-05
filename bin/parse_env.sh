#!/usr/bin/env bash

# CHECK ANY ENV FILE LIKE master.env OR development.env FILE PRESENT
# IF EXIST, REMOVE
count=`ls -1 *.env 2>/dev/null | wc -l`
if [ $count != 0 ]
then
  sudo mv *.env .env
fi

export $(egrep -v '^#' .env | xargs)
