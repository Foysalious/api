<?php namespace Sheba\ShebaAccountKit;

use Namshi\JOSE\JWS;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\Requests\ApiTokenRequest;

class ShebaAccountKit
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getToken(ApiTokenRequest $api_token_request)
    {
        $response = $this->client->getApiToken($api_token_request);
        $data = json_decode($response->getBody());
        if (!$data || !isset($data->api_token)) return null;
        return $data->api_token;
    }

    private function getAccessToken(AccessTokenRequest $access_token_request)
    {
        $response = $this->client->getAccessToken($access_token_request);
        $data = json_decode($response->getBody());
        if (!$data || !isset($data->access_token)) return null;
        return $data->access_token;
    }

    public function getMobile(AccessTokenRequest $access_token_request)
    {
        $token = $this->getAccessToken($access_token_request);
        if ($token == null) return null;
        $jws = JWS::load($token);
        $payload = $jws->getPayload();
        return formatMobile($payload['sub']);
    }
}