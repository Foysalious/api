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
        return json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data, $multipart))->getBody()->getContents(), true);
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
