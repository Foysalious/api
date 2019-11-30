<?php namespace Sheba\ShebaAccountKit;


use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\Requests\ApiTokenRequest;
use Sheba\ShebaAccountKit\Requests\OtpSendRequest;
use Sheba\ShebaAccountKit\Requests\OtpValidateRequest;

class ShebaAccountKit
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getToken(ApiTokenRequest $api_token_request)
    {
        $response = $this->client->getApiToken($api_token_request);
        $data = json_decode($response->getBody());
        if (!$data || !isset($data->api_token)) return null;
        return $data->api_token;
    }

    public function sendOtp(OtpSendRequest $request)
    {
        $response = $this->client->sendOtp($request);
        $data = json_decode($response->getBody());
        if (!$data || !isset($data->can_retry_after)) return null;
        return 1;
    }

    public function validateOtp(OtpValidateRequest $request)
    {
        $response = $this->client->validateOtp($request);
        $data = json_decode($response->getBody());
        if (!$data || !isset($data->authorization_code)) return null;
        return $data->authorization_code;
    }

    public function getAccessToken(AccessTokenRequest $access_token_request)
    {
        $response = $this->client->getAccessToken($access_token_request);
        $data = json_decode($response->getBody());;
        if (!$data || !isset($data->access_token)) return null;
        return $data->access_token;
    }
}