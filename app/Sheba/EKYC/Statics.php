<?php

namespace Sheba\EKYC;

class Statics
{
    CONST ALREADY_VERIFIED = "already_verified";
    CONST VERIFIED         = "verified";
    CONST UNVERIFIED       = "unverified";
    CONST PENDING          = "pending";

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

    public static function faceVerificationResponse($status, $message)
    {
        return [
            'status'  => $status,
            'message' => $message
        ];
    }

    public static function getLivelinessToken()
    {
        return config('ekyc.liveliness_token');
    }

    public static function getLivelinessBaseUrl()
    {
        return config('ekyc.liveliness_base_url');
    }

    public static function getLivelinessConfigurations(): array
    {
        return [
            'liveliness_base_url' => self::getLivelinessBaseUrl(),
            'liveliness_token' => self::getLivelinessToken(),
            'liveliness_duration' => config('ekyc.liveliness_duration'),
            'liveliness_rotation' => config('ekyc.liveliness_rotation')
        ];
    }
}
