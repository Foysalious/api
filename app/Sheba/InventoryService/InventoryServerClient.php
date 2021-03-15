<?php

namespace App\Sheba\InventoryService;

use App\Sheba\InventoryService\Exceptions\InventoryServiceServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\File;
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
        //dd($this->getOptions($data));
        //dd($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));
        //dd($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data))->getBody()->getContents());
        try {
            //dd($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data))->getBody()->getContents());
            return json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data))->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            //throw new \Exception($e->getMessage());
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
        //dd($data['thumb']);
//        $data['thumb'] = base64_encode(file_get_contents($data['thumb']));
//        $options['headers'] = [
//            'Content-Type' => 'application/json',
//            'Accept'       => 'application/json'
//        ];
//        if ($data) {
//            $options['form_params'] = $data;
//            $options['json']        = $data;
//        }
//
//        return $options;

        $options['multipart'] = [
            //'headers' => ['Content-Type' => 'application/json'],
            [
                'name' => 'name',
                'contents' => $data['name']
            ],
            [
                'name' => 'description',
                'contents' => $data['description']
            ],
            [
                'name' => 'is_published',
                'contents' => $data['is_published']
            ],
            [
                'name' => 'thumb',
                'contents' => File::get($data['thumb']->getRealPath()), 'filename' => $data['thumb']->getClientOriginalName()
            ],
            [
                'name' => 'banner',
                'contents' => $data['banner']
            ],
            [
                'name' => 'app_thumb',
                'contents' => $data['app_thumb']
            ],
            [
                'name' => 'app_banner',
                'contents' => $data['app_banner']
            ]
        ];

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