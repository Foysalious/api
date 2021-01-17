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

if [ "$branch_name" = master ]; then
    # Sentry release version create
    VERSION=$(sentry-cli releases propose-version)
    # Create a release
    sentry-cli releases new -p api $VERSION
    # Associate commits with the release
    sentry-cli releases set-commits --auto $VERSION
    # Finalize release
    sentry-cli releases finalize $VERSION
    php artisan set-release-number --release=$VERSION
fi

if [ "$branch_name" = master ]; then
  composer_version="prod"
elif [ "$branch_name" = release ]; then
  composer_version="prod"
else
  composer_version="dev"
fi

sudo ./bin/sheba-composer.sh install $composer_version
sudo php artisan l5-swagger:generate
sudo php artisan swagger-upload-json
sudo php artisan config:clear
php artisan queue:restart