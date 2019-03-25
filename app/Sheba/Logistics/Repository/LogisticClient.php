<?php namespace Sheba\Logistics\Repository;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use mysql_xdevapi\Exception;

class LogisticClient
{
    protected $client;
    protected $baseUrl;
    protected $appKey;
    protected $appSecret;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('logistics.api_url'), '/');
        $this->appKey = config('logistics.app_key');
        $this->appSecret = config('logistics.app_secret');
    }

    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    private function call($method, $uri, $data = null)
    {
        $res = $this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data));
        $res = json_decode($res->getBody()->getContents(), true);

        if ($res['code'] != 200)
            throw new \Exception($res['message']);

        unset($res['code'], $res['message']);
        return $res;
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options['headers'] = [
            'app-key' => $this->appKey,
            'app-secret' => $this->appSecret,
        ];
        if($data) {
            $options['form_params'] = $data;
        }
        return $options;
    }
}