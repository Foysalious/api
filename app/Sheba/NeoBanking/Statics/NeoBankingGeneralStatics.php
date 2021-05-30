<?php namespace Sheba\NeoBanking\Statics;


use Sheba\PushNotificationHandler;

class NeoBankingGeneralStatics
{
    public static function kycData($data)
    {
        return array_except($data, ['manager_resource', 'partner', 'bank_code']);
    }

    public static function populateData($data)
    {
        return [
            "title" => $data->title,
            "description" => $data->description,
            "link" => $data->link,
            "type" => $data->type,
            "event_type" => $data->event_type,
            "event_id" => $data->event_id
        ];
    }

    public static function sendPushNotification($partner, $data)
    {
        $topic = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound = config('sheba.push_notification_sound.manager');
        $notification_data = [
            "title" => $data->title,
            "message" => $data->description,
            "sound" => "notification_sound",
            "event_type" => $data->event_type,
            "event_id" => $data->event_id
        ];

        (new PushNotificationHandler())->send($notification_data, $topic, $channel, $sound);
    }

    public static function primeBankDefaultAccountData()
    {
        return config('neo_banking.default_prime_bank_account');
    }

    public static function gigatechKycValidationData()
    {
        return [
            'bank_code' => 'required|string',
            'nid_no' => 'required|string',
            'dob' => 'required|date',
            'applicant_name_ben' => 'required|string',
            'mobile_number' => 'required|string|mobile:bd',
            'applicant_name_eng' => 'required|string',
            'father_name' => 'required|string',
            'mother_name' => 'required|string',
            'spouse_name' => 'required|string',
            'pres_address' => 'required|string',
            'id_front_name' => 'required|string',
            'id_back_name' => 'required|string',
            'applicant_photo' => 'required|mimes:jpeg,png,jpg',
            'id_front' => 'required|mimes:jpeg,png,jpg',
            'id_back' => 'required|mimes:jpeg,png,jpg',
            'is_kyc_store' => 'required',
        ];
    }

    public static function types($type)
    {
        $data = ['organization_type_list' => ['list' => array_column(constants('PARTNER_OWNER_TYPES'), 'bn'), 'title' => 'প্রতিষ্ঠানের ধরণ সিলেক্ট করুন'], 'business_type_list' => ['list' => constants('PARTNER_BUSINESS_TYPE'),'title'=>'ব্যবসার ধরণ সিলেক্ট করুন']];
        try {
            return  $data[$type];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function accountNumberUpdateData($account_no)
    {
        $data = new \stdClass();
        $data->title = "Account number has been generated";
        $data->description = "Welcome to PBL. Your A/c no. $account_no has been generated. Upon Verification of docs and KYC, your A/c will be activated. You will be duly notified.";
        $data->event_type = "NeoBanking";
        $data->event_id = 1;
        return $data;
    }

    public static function sendCreatePushNotification($partner, $data)
    {
        $topic = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound = config('sheba.push_notification_sound.manager');
        $notification_data = [
            "title" => $data["title"],
            "message" => $data["message"],
            "sound" => "notification_sound",
            "event_type" => $data["event_type"]
        ];

        (new PushNotificationHandler())->send($notification_data, $topic, $channel, $sound);
    }

    public static function formatStatus($status) {
        $data = [];
        if($status->cpv === 'cpv_pending') {
            $data['message'] = config('neo_banking.cpv_pending_message');
            $data['type'] = config('neo_banking.message_type.cpv_pending');
        } else if($status->cpv === 'cpv_unverified') {
            $data['message'] = config('neo_banking.cpv_unverified_message');
            $data['type'] = config('neo_banking.message_type.cpv_unverified');
        } else if($status->cpv === 'cpv_verified') {
            if($status->sign === 'signed') {
                $data['message'] = config('neo_banking.signed_verified_message');
                $data['type'] = config('neo_banking.message_type.cpv_verified');;
            } else {
                $data['message'] = config('neo_banking.unsigned_message');
                $data['type'] = config('neo_banking.message_type.cpv_unsigned');
            }
        }
        return $data;
    }
}
