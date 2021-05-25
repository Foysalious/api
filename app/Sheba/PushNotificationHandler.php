<?php namespace Sheba;

use FCM;
use LaravelFCM\Message\Exceptions\InvalidOptionsException;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\OptionsPriorities;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Message\Topics;

class PushNotificationHandler
{
    private $priority;

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     * @return PushNotificationHandler
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @param        $notification_data
     * @param        $topic
     * @param null   $channel
     * @param string $sound
     * @throws InvalidOptionsException
     */
    public function send($notification_data, $topic, $channel = null, $sound = 'default')
    {
        $str_topic           = $topic;
        $notificationBuilder = new PayloadNotificationBuilder($notification_data['title']);
        $notificationBuilder->setBody($notification_data['message'])->setSound($sound)->setChannelId($channel);
        $notification = $notificationBuilder->build();

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($notification_data);
        $data = $dataBuilder->build();

        $topic   = (new Topics())->topic($topic);
        $options = null;
        if ($this->priority) {
            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setPriority(OptionsPriorities::high);
            $options = $optionBuilder->build();
        }
        $topicResponse = null;
        if (config('sheba.send_push_notifications')) {
            if (strpos($str_topic, config('sheba.push_notification_topic_name.manager')) === 0) {
                $str_topic         = str_replace(config('sheba.push_notification_topic_name.manager'), config('sheba.push_notification_topic_name.manager_new'), $str_topic);
                $topic->conditions = [];
                $topic             = $topic->topic($str_topic);
                $notification      = null;
            }
            $topicResponse = FCM::sendToTopic($topic, $options, $notification, $data);
        }
        return $topicResponse;
    }
}
