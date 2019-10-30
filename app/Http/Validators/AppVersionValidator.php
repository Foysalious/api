<?php namespace App\Http\Validators;

use App\Models\AppVersion;

class AppVersionValidator
{
    public function uniqueAndValidVersion($attribute, $value, $parameters, $validator)
    {
        $version_code = intval(preg_replace("/[^0-9]+/", "", $value));
        $count = AppVersion::where('package_name', $parameters[0])->where('version_code', '>=', $version_code)->count();

        return $count === 0;
    }
}