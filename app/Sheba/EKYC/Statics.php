<?php

namespace Sheba\EKYC;

use App\Models\Partner;

class Statics
{
    CONST ALREADY_VERIFIED = "already_verified";
    CONST VERIFIED         = "verified";
    CONST UNVERIFIED       = "unverified";
    CONST PENDING          = "pending";
    CONST REJECTED         = "rejected";
    CONST INCOMPLETE       = "incomplete";
    CONST SUCCESS_MESSAGE  = "আপনার NID-এর তথ্য এবং ছবি সফলভাবে বাংলাদেশ সরকার কর্তৃক ভেরিফাইড হয়েছে।";
    CONST FAIL_MESSAGE     = "স্বয়ংক্রিয় ভাবে প্রাপ্ত তথ্যে কিছু ভুলের কারনে আপনার NID যাচাই করা সম্ভব হয়নি। %s কর্তৃপক্ষ ম্যানুয়াল ভেরিফিকেশন নিয়ে কাজ করছে। সকল তথ্য ঠিক থাকলে আগামী ৩ কার্যদিবসের মধ্যে ভেরিফিকেশন সম্পন্ন হবে অথবা আপনি চাইলে স্বয়ংক্রিয় ভাবে আরও %s বার চেষ্টা করতে পারেন।";
    CONST FINAL_FAIL_MESSAGE = "আপনি সর্বোচ্চ সংখ্যকবার NID ভেরিফিকেশনের জন্য চেষ্টা করেছেন। %s কর্তৃপক্ষ আপনার NID ভেরিফিকেশন নিয়ে কাজ করছে।আগামী ৩ কার্যদিবসের মধ্যে আপনাকে ফলাফল জানানো হবে। বিস্তারিত জানতে চাইলে 16516 এ কল করুন।";
    CONST PENDING_MESSAGE  = "আপনার NID ভেরিফিকেশন প্রক্রিয়াধীন রয়েছে। ভেরিফিকেশন প্রক্রিয়া দ্রুত করতে চাইলে 16516-এ কল করুন।";
    CONST MAX_PORICHOY_VERIFICATION_ATTEMPT = 3;
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
     * @param $nid_verification_request_count
     * @param $message
     * @param null $avatar
     * @return array
     */
    public static function faceVerificationResponse($status, $nid_verification_request_count, $message, $avatar = null): array
    {
        if($status === self::UNVERIFIED) $status = self::REJECTED;
        $remaining_attempt = self::MAX_PORICHOY_VERIFICATION_ATTEMPT - $nid_verification_request_count;
        $serviceHolder = $avatar instanceof Partner ? "sManager" : "Bondhu";

        if ($nid_verification_request_count < self::MAX_PORICHOY_VERIFICATION_ATTEMPT) {
            $fail_message = sprintf(self::FAIL_MESSAGE, $serviceHolder, convertNumbersToBangla($remaining_attempt, false));
        } else {
            $fail_message = sprintf(self::FINAL_FAIL_MESSAGE, $serviceHolder);
        }
        return [
            'status'  => $status,
            'title'   => $status === self::VERIFIED ? "ভেরিফিকেশন সফল হয়েছে !" : "ভেরিফিকেশন প্রক্রিয়াধীন",
            'message' => $status === self::VERIFIED ? self::SUCCESS_MESSAGE : $fail_message,
            'remaining_attempt' => $remaining_attempt
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
