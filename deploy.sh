#!/bin/sh

# To commit changes of this file
# ------------------------------
# git add deploy.sh
# git update-index --chmod=+x deploy.sh
# git commit -m "Make deploy.sh executable."
# git push

sudo git fetch origin
branch_name="$(git symbolic-ref --short -q HEAD 2>/dev/null)"
reset="sudo git reset --hard origin/"
reset_branch="$reset$branch_name"
eval $reset_branch
sudo composer install --ignore-platform-reqs