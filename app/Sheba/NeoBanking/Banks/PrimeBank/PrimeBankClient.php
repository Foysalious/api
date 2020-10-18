<?php


namespace App\Sheba\NeoBanking\Banks\PrimeBank;


use GuzzleHttp\Client;

class PrimeBankClient
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->client  = (new Client());
        $this->baseUrl = rtrim(config('neo_banking.prime_bank_sbs_url'));
    }

    public function generateToken($user)
    {
        return $this->get("/");
    }

    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    private function call($method, $uri, $data = null)
    {
        $res = $this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data));
        dd($res);
//        $res = json_decode($res->getBody()->getContents(), true);
//        if ($res['code'] != 200) throw new Exception($res['message'],$res['code']);
//
//        unset($res['code'], $res['message']);
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