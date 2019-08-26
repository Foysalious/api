<?php namespace App\Http\Validators;

use Illuminate\Support\Facades\Request;
use App\Models\SmsTemplate;

class SmsTemplateValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        foreach( explode(';', SmsTemplate::find(Request::segment(2))->variables) as $variable) {
            if(!str_contains($value,  "{{" . $variable . "}}")) {
                return false;
            }
        }

        return true;
    }
}