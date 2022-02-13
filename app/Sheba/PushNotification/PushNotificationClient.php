<?php namespace Sheba\PushNotification;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\PushNotification\Exceptions\PushNotificationServerError;

class PushNotificationClient
{
    protected $client;
    protected $baseUrl;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('sheba.services_api_url'), '/');
    }

    /**
     * @throws PushNotificationServerError
     */
    private function call($method, $uri, $data = null, $multipart = false)
    {
        try {
            return json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data, $multipart))->getBody()->getContents(), true);
        } catch (GuzzleException $e) {

            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = $res->getBody()->getContents();
            if ($http_code > 399 && $http_code < 500) throw new PushNotificationServerError($message, $http_code);
            throw new PushNotificationServerError($e->getMessage(), $http_code);
        }
    }

    private function makeUrl($uri): string
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null, $multipart = false): array
    {
        $options['headers'] = [
            'Accept' => 'application/json',
            'app-key' => config('sheba.notification_services_app_key'),
            'app-secret' => config('sheba.notification_services_app_secret'),
        ];
        if (!$data) return $options;
        if ($multipart) {
            $options['multipart'] = $data;
        } else {
            $options['form_params'] = $data;
            $options['json'] = $data;
        }
        return $options;
    }

    /**
     * @throws PushNotificationServerError
     */
    public function post($uri, $data, $multipart = false)
    {
        return $this->call('post', $uri, $data, $multipart);
    }
}
