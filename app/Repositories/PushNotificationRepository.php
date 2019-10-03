<?php

namespace App\Repositories;

use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use LaravelFCM\Message\Topics;

class PushNotificationRepository
{

    public function send($notification_data, $topic)
    {
        $notificationBuilder = new PayloadNotificationBuilder($notification_data['title']);
        $notificationBuilder->setBody($notification_data['message'])->setSound('default');
        $notification = $notificationBuilder->build();

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($notification_data);
        $data = $dataBuilder->build();

        $topic = (new Topics())->topic($topic);

        if (constants('send_push_notifications')) {
            FCM::sendToTopic($topic, null, $notification, $data);
        }

    }
}