<?php namespace Sheba\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class Accounts
{
    private $client, $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = rtrim(config('account.account_url'), '/');
    }


    /**
     * @param $bank_user
     * @return array|mixed
     */
    public function getToken($bank_user)
    {
        try {
            $uri = $this->baseUrl . '/api/v3/token/generate?type=bankUser&token=' . $bank_user->remember_token . '&type_id=' . $bank_user->id;
            $response = $this->client->get($uri)->getBody()->getContents();
            return json_decode($response, true);
        } catch (GuzzleException $e) {
            return ['code' => 403, 'message' => $e->getMessage()];
        }
    }

    public function  getJWTToken($type,$type_id,$remember_token)
    {
        try {
            $uri = $this->baseUrl . '/api/v3/token/generate?type='.$type.'&token=' . $remember_token . '&type_id=' . $type_id;
            $response = $this->client->get($uri)->getBody()->getContents();
            return json_decode($response, true);
        } catch (GuzzleException $e) {
            return ['code' => 403, 'message' => $e->getMessage()];
        }

    }
}
