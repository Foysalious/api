#!/bin/sh

## To commit changes of this file
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

# Sentry release version create
VERSION=$(sentry-cli releases propose-version)
# Create a release
sentry-cli releases new -p api $VERSION
# Associate commits with the release
sentry-cli releases set-commits --auto $VERSION
# Finalize release
sentry-cli releases finalize "$VERSION"

sudo composer install --ignore-platform-reqs --no-interaction
sudo php artisan config:clear
php artisan queue:restart