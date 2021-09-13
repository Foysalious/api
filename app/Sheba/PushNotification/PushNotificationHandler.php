<?php namespace Sheba\PushNotification;


class PushNotificationHandler
{
    /**
     * @param $notification_data
     * @param $topic
     * @return mixed|null
     * @throws Exceptions\PushNotificationServerError
     */
    public function send($notification_data, $topic)
    {
        $topicResponse = null;
        if (config('sheba.send_push_notifications')) {
            /** @var PushNotificationClient $client */
            $client = app(PushNotificationClient::class);
            $data = [
                "topic" => $topic,
                "title" => $notification_data["title"],
                "body" => $notification_data["message"],
                "data" => $notification_data,
                "account_id" => config('sheba.sheba_push_notifications_account_id')
            ];
            $url = 'api/vendors/' . config('sheba.sheba_services_vendor_id') . '/notification/send';
            $topicResponse = $client->post($url, $data);
        }
        return $topicResponse;
    }
}