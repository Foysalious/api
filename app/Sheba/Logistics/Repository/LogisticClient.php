<?php namespace Sheba\Logistics\Repository;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\Logistics\Exceptions\LogisticServerError;

class LogisticClient
{
    protected $client;
    protected $baseUrl;
    protected $appKey;
    protected $appSecret;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('logistics.api_url'), '/');
        $this->appKey = config('logistics.app_key');
        $this->appSecret = config('logistics.app_secret');
    }

    /**
     * @param $uri
     * @return mixed
     * @throws LogisticServerError
     */
    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     * @throws LogisticServerError
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     * @throws LogisticServerError
     */
    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return mixed
     * @throws LogisticServerError
     */
    private function call($method, $uri, $data = null)
    {
        try {
            $res = decodeGuzzleResponse($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));
            if ($res['code'] != 200) throw new LogisticServerError($res['message']);
            unset($res['code'], $res['message']);
            return $res;
        } catch (GuzzleException $e) {
            $res = decodeGuzzleResponse($e->getResponse());
            if ($res['code'] == 400) throw new LogisticServerError($res['message']);
            throw new LogisticServerError($e->getMessage());
        }
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options['headers'] = [ 'app-key' => $this->appKey, 'app-secret' => $this->appSecret ];
        if ($data) {
            $options['form_params'] = $data;
        }
        return $options;
    }
}