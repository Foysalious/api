<?php namespace Sheba;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use LaravelFCM\Message\Topics;

class PushNotificationHandler
{
    public function send($notification_data, $topic, $channel = null, $sound = 'default')
    {
        $str_topic = $topic;
        $notificationBuilder = new PayloadNotificationBuilder($notification_data['title']);
        $notificationBuilder->setBody($notification_data['message'])->setSound($sound)->setChannelId($channel);
        $notification = $notificationBuilder->build();

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($notification_data);
        $data = $dataBuilder->build();

        $topic = (new Topics())->topic($topic);

        if (config('sheba.send_push_notifications')) {
            $topicResponse = FCM::sendToTopic($topic, null, $notification, $data);
            if (strpos($str_topic, config('sheba.push_notification_topic_name.manager')) == 0) {
                $str_topic = str_replace(config('sheba.push_notification_topic_name.manager'), config('sheba.push_notification_topic_name.manager_new'), $str_topic);
                $topic = (new Topics())->topic($str_topic);
                $topicResponse = FCM::sendToTopic($topic, null, null, $data);
            }
        }

        //$topicResponse->isSuccess();
        //$topicResponse->shouldRetry();
        //$topicResponse->error();
    }
}