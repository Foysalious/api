<?php

namespace Sheba\EKYC;

class Statics
{
    public static function faceVerificationValidate(): array
    {
        return [
            'nid' => 'required|digits_between:10,17',
            'person_photo' => 'required | string',
            'dob' => 'required|date_format:Y/m/d'
        ];
    }

    public static function storeNidOcrDataValidation(): array
    {
        return [
            'id_front' => 'required | mimes:jpeg,jpg,png',
            'id_back' => 'required | mimes:jpeg,jpg,png'
        ];
    }
}