<?php namespace App\Http\Validators;


use Sheba\Dal\AppVersion\AppVersionRepository;

class AppVersionValidator
{
    public function uniqueAndValidVersion($attribute, $value, $parameters, $validator)
    {
        $version_code = intval(preg_replace("/[^0-9]+/", "", $value));

        /** @var AppVersionRepository $app_versions */
        $app_versions = app(AppVersionRepository::class);

        return !$app_versions->hasSameOrLaterVersionOfPackage($parameters[0], $version_code);
    }
}
