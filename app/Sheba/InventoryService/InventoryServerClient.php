<?php

namespace App\Sheba\InventoryService;

use App\Sheba\InventoryService\Exceptions\InventoryServiceServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;

class InventoryServerClient
{
    protected $client;
    protected $baseUrl;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('inventory_service.api_url'), '/');
    }

    public function get($uri)
    {
        return $this->call('get', $uri);
    }


    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return mixed
     * @throws InventoryServiceServerError
     */
    private function call($method, $uri, $data = null)
    {
        try {
            return json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data))->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = $res->getBody()->getContents();
            if ($http_code > 399 && $http_code < 500) throw new InventoryServiceServerError($message, $http_code);
            throw new InventoryServiceServerError($e->getMessage(),$http_code);
        }
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options['headers'] = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json'
        ];
        if ($data) {
            $options['form_params'] = $data;
            $options['json']        = $data;
        }
        return $options;
    }

    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    /**
     * @param $uri
     * @param $data
     * @return array|object|string|null
     * @throws ExpenseTrackingServerError
     */
    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }

    /**
     * @param $uri
     * @return array|object|string|null
     * @throws ExpenseTrackingServerError
     */
    public function delete($uri)
    {
        return $this->call('DELETE', $uri);
    }


}