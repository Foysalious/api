<?php

namespace Sheba\EKYC;

class Statics
{
    CONST ALREADY_VERIFIED = "already_verified";
    CONST VERIFIED         = "verified";
    CONST UNVERIFIED       = "unverified";
    CONST PENDING          = "pending";
    CONST REJECTED         = "rejected";
    CONST SUCCESS_MESSAGE  = "আপনার NID-এর তথ্য এবং ছবি সফলভাবে বাংলাদেশ সরকার কর্তিক ভেরিফাইড হয়েছে।";
    CONST FAIL_MESSAGE     = "দুঃখিত! আপনার আবেদনটি সফল হয়নি। যেকোনো তথ্যের জন্য কল করুন ১৬৫১৬ নাম্বারে।";

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

    /**
     * @param $status
     * @param $message
     * @return array
     */
    public static function faceVerificationResponse($status, $message): array
    {
        if($status === self::UNVERIFIED) $status = self::REJECTED;
        return [
            'status'  => $status,
            'title'   => $status === self::VERIFIED ? "আবেদন সফল হয়েছে !" : "আবেদন সফল হয়নি !",
            'message' => $status === self::VERIFIED ? self::SUCCESS_MESSAGE : self::FAIL_MESSAGE
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
