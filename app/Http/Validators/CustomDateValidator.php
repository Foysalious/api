<?php namespace App\Http\Validators;

class CustomDateValidator
{
    public function afterOrEqual($attribute, $value, $parameters, $validator)
    {
        return strtotime($validator->getData()[$parameters[0]]) <= strtotime($value);
    }

    public function beforeOrEqual($attribute, $value, $parameters, $validator)
    {
        return strtotime($validator->getData()[$parameters[0]]) >= strtotime($value);
    }

    public function afterOrEqualIf($attribute, $value, $parameters, $validator)
    {
        $values = array_splice($parameters, 2);
        if (in_array($validator->getData()[$parameters[1]], $values)) {
            return true;
        }
        return strtotime($validator->getData()[$parameters[0]]) <= strtotime($value);
    }

    public function dateIfNot($attribute, $value, $parameters, $validator)
    {
        $values = array_splice($parameters, 1);
        if (in_array($validator->getData()[$parameters[0]], $values)) {
            return true;
        } else {
            $chunk = explode('-', $value);
            if (count($chunk) != 3) {
                $chunk = explode('/', $value);
                if ($chunk != 3) {
                    return false;
                }
            }

            return checkdate($chunk[1], $chunk[2], $chunk[1]);
        }

    }
}