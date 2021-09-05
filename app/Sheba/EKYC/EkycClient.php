<?php namespace Sheba\EKYC;

use GuzzleHttp\Client;
use Exception;
use Throwable;

class EkycClient
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = (new Client());
        $this->baseUrl = rtrim(config('sheba.ekyc_url') . '/api/v1');
    }

    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    private function call($method, $uri, $data = null)
    {
        $res = $this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data));
        $res = json_decode($res->getBody()->getContents(), true);
        if ($res['code'] != 200)
            throw new Exception($res['message']);
        unset($res['code'], $res['message']);
        return $res;
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options = [];
        if ($data)
            $options['form_params'] = $data;
        return $options;
    }

    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }
}