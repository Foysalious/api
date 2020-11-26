<?php namespace Sheba\OAuth2;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AccountServerClient
{
    /** @var Client */
    private $httpClient;
    /** @var string */
    private $baseUrl;

    public function __construct(Client $client)
    {
        $this->httpClient = $client;
        $this->baseUrl = rtrim(config('account.account_url'), '/');
    }

    /**
     * @param $uri
     * @return array
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return array
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     * @throws WrongPinError
     */
    private function call($method, $uri, $data = null)
    {
        try {
            $res = decodeGuzzleResponse($this->httpClient->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));
            if ($res['code'] == 403 && in_array('login_wrong_pin_count', $res)) throw new WrongPinError($res['login_wrong_pin_count'], $res['remaining_hours_to_unblock'], $res['message'], $res['code']);
            if ($res['code'] > 399 && $res['code'] < 500) throw new AccountServerAuthenticationError($res['message'], $res['code']);
            if ($res['code'] != 200) throw new AccountServerNotWorking($res['message']);
            return $res;
        } catch (GuzzleException $e) {
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
     * @return mixed
     */
    private function getOptions($data = null)
    {
        $headers = getShebaRequestHeader();
        $options = [];

        if (!$headers->isEmpty()) $options['headers'] = $headers->toArray();
        if ($data) $options['form_params'] = $data;

        return $options;
    }

    /**
     * @param $uri
     * @param $data
     * @return array
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }
}
