<?php namespace App\Http\Validators;

use App\Helper\BangladeshiMobileValidator;

class MobileNumberValidator
{
    private $countryCodes = [];

    public function __construct()
    {
        $this->countryCodes = [
            "bd" => "Bangladeshi",
        ];
    }

    public function validate($attribute, $value, $parameters, $validator)
    {
        $method = "validate" . $this->countryCodes[$parameters[0]];
        return $this->$method($value);
    }

    public function validateBangladeshi($value)
    {
        return BangladeshiMobileValidator::validate($value);
    }
}