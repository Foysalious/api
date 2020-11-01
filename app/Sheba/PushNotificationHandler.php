<?php namespace Sheba;

use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Message\Topics;

class PushNotificationHandler
{
    public function send($notification_data, $topic, $channel = null, $sound = 'default')
    {
        $str_topic           = $topic;
        $notification = ( new PayloadNotificationBuilder())->setTitle($notification_data['title'])->setBody($notification_data['message'])->setSound($sound)->setChannelId($channel)->build();
        $data =(new PayloadDataBuilder())->addData($notification_data)->build();
        $topics             = (new Topics());
        $topics->conditions = [];
        $topic              = $topics->topic($topic);
        $topicResponse      = null;
        if (config('sheba.send_push_notifications')) {
            $topicResponse = FCM::sendToTopic($topic, null, $notification, $data);
            if (strpos($str_topic, config('sheba.push_notification_topic_name.manager')) == 0) {
                $str_topic     = str_replace(config('sheba.push_notification_topic_name.manager'), config('sheba.push_notification_topic_name.manager_new'), $str_topic);
                $topic         = (new Topics())->topic($str_topic);
                $topicResponse = FCM::sendToTopic($topic, null, null, $data);
            }
        }
        return $topicResponse;
    }
}
