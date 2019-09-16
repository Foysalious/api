<?php


namespace App\Http\Validators;


class NidNumberValidator
{
    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        $length = strlen($value);
        return in_array($length, [10, 13, 17]);
    }
}
