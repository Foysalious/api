<?php namespace Sheba\EKYC;

use GuzzleHttp\Client;
use Exception;
use Throwable;

class EkycClient
{
    protected $userId;
    protected $clientId;
    protected $clientSecret;
    protected $userType;
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = (new Client());
        $this->baseUrl = rtrim(config('sheba.ekyc_url', 'https://ekyc.dev-sheba.xyz') . '/api/v1');
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
//        dd($res);
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
        $options['headers'] = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'CLIENT-ID' => $this->clientId,
            'CLIENT-SECRET' => $this->clientSecret
        ];
        if ($data) {
            $options['json'] = $data;
        }
        return $options;
    }

    public function setUserType($userType)
    {
        $this->userType = $userType;
        return $this;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }
}