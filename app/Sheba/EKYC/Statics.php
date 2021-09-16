<?php

namespace Sheba\EKYC;

class Statics
{
    CONST ALREADY_VERIFIED = "already_verified";
    CONST VERIFIED         = "verified";
    CONST UNVERIFIED       = "unverified";
    CONST PENDING          = "pending";
    CONST REJECTED         = "rejected";

    public static function faceVerificationValidate(): array
    {
        return [
            'nid' => 'required|digits_between:10,17',
            'person_photo' => 'required | mimes:jpeg,jpg,png',
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
        if($status === self::UNVERIFIED) $status = self::REJECTED;
        return [
            'status'  => $status,
            'title'   => $status === self::VERIFIED ? "আবেদন সফল হয়েছে !" : "আবেদন সফল হয়নি !",
            'message' => $status === self::VERIFIED ? "আপনার NID-এর তথ্য সফলভাবে sManager কর্তৃপক্ষের নিকট পাঠানো হয়েছে। আপনাকে ৭২ ঘণ্টার মধ্যে জানানো হবে। যেকোনো তথ্যের জন্য কল করুন ১৬৫১৬ নাম্বারে।"
                : "দুঃখিত! আপনার আবেদনটি সফল হয়নি। যেকোনো তথ্যের জন্য কল করুন ১৬৫১৬ নাম্বারে।"
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
