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
eval "$reset_branch"

if [ "$branch_name" = master ]; then
  VERSION=$(sentry-cli releases propose-version)
  sentry-cli releases new -p api "$VERSION"
  sentry-cli releases set-commits --auto "$VERSION"
  sentry-cli releases finalize "$VERSION"
  php artisan set-release-number --release="$VERSION"
fi

sudo composer install --ignore-platform-reqs --no-interaction

# sudo php artisan l5-swagger:generate
# sudo php artisan swagger-upload-json

sudo php artisan config:clear
php artisan queue:restart
