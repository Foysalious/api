<?php namespace Sheba\OAuth2;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AccountServerClient
{
    /** @var Client */
    private $httpClient;
    /** @var string */
    private $baseUrl;
    /** @var string */
    private $token;

    public function __construct(Client $client)
    {
        $this->httpClient = $client;
        $this->baseUrl = rtrim(config('account.account_url'), '/');
    }

    /**
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @param $uri
     * @param $headers
     * @return array
     * @throws AccountServerNotWorking|AccountServerAuthenticationError|WrongPinError
     */
    public function get($uri, $headers = null)
    {
        return $this->call('get', $uri, null, $headers);
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @param null $headers
     * @return array
     * @throws AccountServerNotWorking|AccountServerAuthenticationError|WrongPinError
     */
    private function call($method, $uri, $data = null, $headers = null)
    {
        try {
            $res = $this->httpClient->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data, $headers));
            $res = decodeGuzzleResponse($res);
            if ($res == null) return [];

            if ($res['code'] == 403 && in_array('login_wrong_pin_count', $res)) throw new WrongPinError($res['login_wrong_pin_count'], $res['remaining_hours_to_unblock'], $res['message'], $res['code']);
            if ($res['code'] > 399 && $res['code'] < 500) throw new AccountServerAuthenticationError($res['message'], $res['code']);
            if ($res['code'] != 200) throw new AccountServerNotWorking($res['message']);
            return $res;
        } catch (GuzzleException $e) {
            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = decodeGuzzleResponse($res);
            if ($http_code > 399 && $http_code < 500) throw new AccountServerAuthenticationError($message, $http_code);
            throw new AccountServerNotWorking($e->getMessage());
        }
    }

    /**
     * @param $uri
     * @return string
     */
    private function makeUrl($uri)
    {
        return $this->baseUrl . '/' . trim($uri, '/');
    }

    /**
     * @param null $data
     * @param null $headers
     * @return mixed
     */
    private function getOptions($data = null, $headers = null)
    {
        $sheba_headers = getShebaRequestHeader();
        $options = [];

        if ($data) $options['form_params'] = $data;

        $options['headers'] = [];
        if ($this->token)  $options['headers'] += ['Authorization' => 'Bearer ' . $this->token];
        if ($headers) $options['headers'] += $headers;
        if (!$sheba_headers->isEmpty()) $options['headers'] += $sheba_headers->toArray();
        if (empty($options['headers'])) unset($options['headers']);

        return $options;
    }

    /**
     * @param $uri
     * @param $data
     * @param $headers
     * @return array
     * @throws AccountServerNotWorking|AccountServerAuthenticationError|WrongPinError
     */
    public function post($uri, $data, $headers = null)
    {
        return $this->call('post', $uri, $data, $headers);
    }
}
