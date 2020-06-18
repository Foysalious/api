<?php namespace Sheba\ShebaAccountKit;

use GuzzleHttp\Client as GuzzleHttpClient;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\Requests\ApiTokenRequest;
use Sheba\ShebaAccountKit\Requests\OtpSendRequest;
use Sheba\ShebaAccountKit\Requests\OtpValidateRequest;

class Client
{
    private $appSecret;
    private $appId;
    private $endPoint;
    private $httpClient;

    public function __construct()
    {
        $this->appId = config('sheba_accountkit.app_id');
        $this->appSecret = config('sheba_accountkit.app_secret');
        $this->endPoint = config('sheba_accountkit.end_point');
        $this->httpClient = new GuzzleHttpClient();
    }

    public function getApiToken(ApiTokenRequest $api_token_request)
    {
        return $this->httpClient->request('GET', $this->endPoint . '/api-token', [
            'query' => ['app_id' => $api_token_request->getAppId(), 'app_secret' => $this->appSecret]
        ]);
    }

    public function sendOtp(OtpSendRequest $sms_send_request)
    {
        return $this->httpClient->request('POST', $this->endPoint . '/shoot-otp', [
            'json' => [
                'mobile' => $sms_send_request->getMobile(),
                'app_id' => $sms_send_request->getAppId(),
                'api_token' => $sms_send_request->getApiToken()
            ], 'headers' => [
                'content-type' => 'application/json'
            ]
        ]);
    }

    public function validateOtp(OtpValidateRequest $otp_validate_request)
    {
        return $this->httpClient->request('POST', $this->endPoint . '/validate-otp', [
            'json' => [
                'otp' => $otp_validate_request->getOtp(),
                'app_id' => $otp_validate_request->getAppId(),
                'api_token' => $otp_validate_request->getApiToken()
            ], 'headers' => [
                'content-type' => 'application/json'
            ]
        ]);
    }

    public function getAccessToken(AccessTokenRequest $access_token_request)
    {
        return $this->httpClient->request('GET', $this->endPoint . '/access-token', [
            'query' => [
                'app_id' => $this->appId, 'app_secret' => $this->appSecret, 'authorization_code' => $access_token_request->getAuthorizationCode()
            ]
        ]);
    }
}
