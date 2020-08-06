<?php


namespace Sheba\Loan;


use Sheba\PushNotificationHandler;

class Notifications
{
    public static function sendLoanNotification($title,$eventType,$eventId){
        notify()->departments([
            9,
            13
        ])->send([
            "title"      => $title,
            'link'       => env('SHEBA_BACKEND_URL') . "/sp-loan/$eventId",
            "type"       => notificationType('Info'),
            "event_type" => $eventType,
            "event_id"   => $eventId
        ]);
    }
    public static function sendPushNotification($old_status, $new_status, $partner_bank_loan){
        $class   = class_basename($partner_bank_loan);
        $topic   = config('sheba.push_notification_topic_name.manager') . $partner_bank_loan->partner_id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound   = config('sheba.push_notification_sound.manager');
        $notification_data = [
            "title" => 'New Order',
            "message" => "প্রিয় X আপনার একটি নতুন অর্ডার রয়েছে, অনুগ্রহ করে ম্যানেজার অ্যাপ থেকে অর্ডারটি একসেপ্ট করুন",
            "event_type" => 'PartnerOrder',
            "event_id" => 98936,
            "link" => "new_order",
            "sound" => "notification_sound"
        ];

        (new PushNotificationHandler())->send($notification_data, $topic, $channel, $sound);
    }
}
