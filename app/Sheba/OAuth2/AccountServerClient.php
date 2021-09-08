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
        $options = $this->addHeadersToOption([], $headers);
        return $this->call('get', $uri, $options);
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
        $options = $this->addHeadersToOption([ 'form_params' => $data ], $headers);
        return $this->call('post', $uri, $options);
    }

    /**
     * @param $uri
     * @param $data
     * @param $headers
     * @return array
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     */
    public function put($uri, $data, $headers = null)
    {
        $options = $this->addHeadersToOption([ 'query' => $data ], $headers);
        return $this->call('put', $uri, $options);
    }

    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @return array
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     */
    private function call($method, $uri, $options = [])
    {
        try {
            $res = $this->httpClient->request(strtoupper($method), $this->makeUrl($uri), $options);
            $res = decodeGuzzleResponse($res);
            if ($res == null) return [];
            if (!is_array($res)) return $res;
            if (!array_key_exists('code', $res)) return $res;
            
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

    private function addHeadersToOption($options, $headers)
    {
        $options['headers'] = $this->getHeaders($headers);
        if (empty($options['headers'])) unset($options['headers']);
        return $options;
    }

    private function getHeaders($extra_headers = null)
    {
        $sheba_headers = getShebaRequestHeader();
        $headers = [];
        if ($this->token) $headers += ['Authorization' => 'Bearer ' . $this->token];
        if ($extra_headers) $headers += $extra_headers;
        if (!$sheba_headers->isEmpty())$headers += $sheba_headers->toArray();
        return $headers;
    }
}
