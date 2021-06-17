<?php namespace App\Sheba\PosOrderService;


use App\Sheba\InventoryService\Exceptions\InventoryServiceServerError;
use App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PosOrderServerClient
{
    protected $client;
    public $baseUrl;
    public $token;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('pos_order_service.api_url'), '/');
    }

    /**
     * @param mixed $token
     * @return PosOrderServerClient
     */
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
     * @param $method
     * @param $uri
     * @param null $data
     * @param bool $multipart
     * @return mixed
     * @throws InventoryServiceServerError
     * @throws PosOrderServiceServerError
     */
    private function call($method, $uri, $data = null, $multipart = false)
    {
        try {
            dd(json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data, $multipart))->getBody()->getContents(), true));
        } catch (GuzzleException $e) {
            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = $res->getBody()->getContents();
            if ($http_code > 399 && $http_code < 500) throw new PosOrderServiceServerError($message, $http_code);
            throw new PosOrderServiceServerError($e->getMessage(), $http_code);
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

    /**
     * @param $uri
     * @param $data
     * @param bool $multipart
     * @return array|object|string|null
     * @throws InventoryServiceServerError
     */
    public function put($uri, $data, $multipart = false)
    {
        return $this->call('put', $uri, $data, $multipart);
    }

    /**
     * @param $uri
     * @return array|object|string|null
     * @throws InventoryServiceServerError
     */
    public function delete($uri)
    {
        return $this->call('DELETE', $uri);
    }


}