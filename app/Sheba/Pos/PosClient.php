<?php


namespace App\Sheba\Pos;


use App\Sheba\Pos\Exceptions\PosClientException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Log;

class PosClient
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = config('sheba.api_url');
        $this->apiKey = 'sheba.xyz';
    }

    /**
     * @param $uri
     * @return array|object|string|void|null
     */
    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return array|object|string|void|null
     */
    private function call($method, $uri, $data = null)
    {
        try {
            $res = decodeGuzzleResponse($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));
//            if ($res['code'] != 200)
//                throw new PosClientException($res['message']);
            unset($res['code'], $res['message']);
            Log::info('online payment', $res);
            return $res;
        } catch (GuzzleException $e) {
            Log::error('online payment', $e);
//            $res = decodeGuzzleResponse($e->getResponse());
//            if ($res['code'] == 400)
//                throw new PosClientException($res['message']);
//            throw new PosClientException($e->getMessage());
        }
    }

    /**
     * @param $uri
     * @return string
     */
    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options['headers'] = [
            'Content-Type' => 'application/json',
            'api-key'    => $this->apiKey,
            'Accept'       => 'application/json'
        ];
        if ($data) {
            $options['form_params'] = $data;
            $options['json']        = $data;
        }
        return $options;
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     */
    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }

    /**
     * @param $uri
     * @return mixed
     */
    public function delete($uri)
    {
        return $this->call('DELETE', $uri);
    }
}
