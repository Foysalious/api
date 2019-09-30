<?php namespace App\Http\Controllers;


use GuzzleHttp\Exception\RequestException;

class FacebookAccountKit
{
    /** @var \GuzzleHttp\Client $client contains the client for sending HTTP requests */
    private $client;

    /** @var string $appId contains the application ID of the facebook app */
    private $appId;

    /** @var string $appSecret contains the secret key of the facebook app */
    private $appSecret;

    /** @var string $endPointUrl contains the endpoint url to hit for the facebook app */
    private $endPointUrl;

    /** @var string $tokenExchangeUrl contains the url for extracting the access token for a specific facebook authentication code */
    private $tokenExchangeUrl;

    /**
     * Create a new FacebookAccountKit instance.
     */
    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
        $this->appId = config('accountkit.app_id');
        $this->appSecret = config('accountkit.app_secret');
        $this->endPointUrl = config('accountkit.end_point');
        $this->tokenExchangeUrl = config('accountkit.tokenExchangeUrl');
    }

    /**
     * Retrieve the mobile/email for an authentication code.
     *
     * @param $code
     * @return array
     */
    public function authenticateKit($code)
    {
        $userAccessToken = $this->retrieveUserAccessToken($code);
        if ($userAccessToken == false) {
            return false;
        } else {
            try {
                $request = $this->client->request('GET', $this->endPointUrl . $userAccessToken);
            } catch (RequestException $e) {
                return false;
            }
            $data = json_decode($request->getBody());

            $credentials['mobile'] = null;
            $credentials['email'] = null;
            $credentials['email_or_mobile'] = null;

            $userId = $data->id;
            if (isset($data->phone)) {
                $credentials['email_or_mobile'] = $data->phone->number;
                $credentials['mobile'] = $data->phone->number;
            } else if (isset($data->email)) {
                $credentials['email_or_mobile'] = $data->email->address;
                $credentials['email'] = $data->email->address;
            }

            return $credentials;
        }
    }

    /**
     * Extract the access token for a specific authentication code
     * @param $code
     * @return string
     */
    private function retrieveUserAccessToken($code)
    {
        $url = $this->tokenExchangeUrl . 'grant_type=authorization_code' .
            '&code=' . $code .
            "&access_token=AA|$this->appId|$this->appSecret";
        try {
            $apiRequest = $this->client->request('GET', $url);
        } catch (RequestException $e) {
            return false;
        }
        $body = json_decode($apiRequest->getBody());
        return $body->access_token;
    }
}