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
    public static function sendStatusChangeNotification($old_status, $new_status, $partner_bank_loan){
        $class   = class_basename($partner_bank_loan);
        $topic   = config('sheba.push_notification_topic_name.manager') . $partner_bank_loan->partner_id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound   = config('sheba.push_notification_sound.manager');
        $notification_data = [
            "title" => 'Loan status changed',
            "message" => "Loan status has been updated from $old_status to $new_status",
            "sound" => "notification_sound",
            "event_type" => "App\\Models\\$class",
            "event_id" => $partner_bank_loan->id
        ];

        (new PushNotificationHandler())->send($notification_data, $topic, $channel, $sound);
    }
}
