<?php

namespace App\Sheba\InventoryService;

use App\Sheba\InventoryService\Exceptions\InventoryServiceServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class InventoryServerClient
{
    protected $client;
    protected $baseUrl;
    private $token;

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
     * @param bool $multipart
     * @return mixed
     * @throws InventoryServiceServerError
     */
    private function call($method, $uri, $data = null, $multipart = false)
    {
        try {
            return json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data, $multipart))->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = $res->getBody()->getContents();
            if ($http_code > 399 && $http_code < 500) throw new InventoryServiceServerError($message, $http_code);
            throw new InventoryServiceServerError($e->getMessage(), $http_code);
        }
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null, $multipart = false)
    {
        $options['headers'] = [
            'Accept' => 'application/json',
//            'portal-name' => getShebaRequestHeader()->toArray()['portal-name'],
//            'Version-Code' => getShebaRequestHeader()->toArray()['Version-Code']
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

    private function addHeadersToOption($options, $headers)
    {
        $options['headers'] = $this->getHeaders($headers);
        if (empty($options['headers'])) unset($options['headers']);
        return $options;
    }

    private function getHeaders($extra_headers = null)
    {
        $sheba_headers = getShebaRequestHeader();
        $headers = [];
        if ($this->token) $headers += ['Authorization' => 'Bearer ' . $this->token];
        if ($extra_headers) $headers += $extra_headers;
        if (!$sheba_headers->isEmpty()) $headers += $sheba_headers->toArray();
        return $headers;
    }

}
