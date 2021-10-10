<?php namespace App\Sheba\Partner\Delivery;

use App\Sheba\Partner\Delivery\Exceptions\DeliveryServiceServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DeliveryServerClient
{
    protected $client;
    protected $baseUrl;
    protected $token;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('pos_delivery.api_url'), '/');
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }


    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    /**
     * @throws DeliveryServiceServerError
     */
    private function call($method, $uri, $data = null, $multipart = false)
    {
        try {
            return json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data, $multipart))->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = $res->getBody()->getContents();
            $decoded_message = json_decode($message,true);
            if(isset($decoded_message['errors']))
                $message = array_values($decoded_message['errors'])[0][0];
            else
                $message = $decoded_message['message'];
            if ($http_code > 399 && $http_code < 500) throw new DeliveryServiceServerError($message, $http_code);
            throw new DeliveryServiceServerError($e->getMessage(), $http_code);
        }
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null, $multipart = false)
    {
        $options['headers'] = [
            'Accept' => 'application/json'
        ];
        if ($this->token)  $options['headers'] += ['Authorization' => 'Bearer ' . $this->token];
        if (!$data) return $options;
        if ($multipart) {
            $options['multipart'] = $data;
        } else {
            $options['form_params'] = $data;
            $options['json'] = $data;
        }
        return $options;
    }

    public function post($uri, $data, $multipart = false)
    {
        return $this->call('post', $uri, $data, $multipart);
    }

    public function put($uri, $data, $multipart = false)
    {
        return $this->call('put', $uri, $data, $multipart);
    }

    public function delete($uri)
    {
        return $this->call('DELETE', $uri);
    }


}
