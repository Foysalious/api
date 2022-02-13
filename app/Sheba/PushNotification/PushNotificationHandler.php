<?php namespace Sheba\PushNotification;


class PushNotificationHandler
{

    public function send($topic, $notification = null, $data = null, $priority = null)
    {
        $topicResponse = null;
        if (config('sheba.send_push_notifications')) {
            /** @var PushNotificationClient $client */
            $client = app(PushNotificationClient::class);
            $data = [
                "topic" => $topic,
                "data" => json_encode($data),
                'channel' => 'firebase'
            ];
            if ($notification && $notification['title']) $data['title'] = $notification['title'];
            if ($notification && $notification['body']) $data['body'] = $notification['body'];
            if ($priority) $data['priority'] = $priority;
            $url = 'api/vendors/' . config('sheba.sheba_services_vendor_id') . '/notification/send';
            $topicResponse = $client->post($url, $data);
        }
        return $topicResponse;
    }
}