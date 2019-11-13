<?php namespace Sheba\FraudDetection\Repository;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\FraudDetection\Exceptions\FraudDetectionServerError;

class FraudDetectionClient
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('fraud_detection.api_url'), '/');
        $this->apiKey = config('fraud_detection.api_key');
    }

    /**
     * @param $uri
     * @return array
     * @throws FraudDetectionServerError
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
     * @throws FraudDetectionServerError
     */
    private function call($method, $uri, $data = null)
    {
        try {
            $res = decodeGuzzleResponse($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));
            if ($res['code'] != 200) throw new FraudDetectionServerError($res['message']);
            unset($res['code'], $res['message']);
            return $res;
        } catch (GuzzleException $e) {
            $res = decodeGuzzleResponse($e->getResponse());
            if ($res['code'] == 400) throw new FraudDetectionServerError($res['message']);
            throw new FraudDetectionServerError($e->getMessage());
        }
    }

    /**
     * @param $uri
     * @return string
     */
    private function makeUrl($uri)
    {
        return $this->baseUrl . $uri;
    }

    /**
     * @param null $data
     * @return mixed
     */
    private function getOptions($data = null)
    {
        $options['headers'] = ['Content-Type' => 'application/json', 'x-api-key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($data) {
            $options['form_params'] = $data;
            $options['json'] = $data;
        }
        return $options;
    }

    /**
     * @param $uri
     * @param $data
     * @return array
     * @throws FraudDetectionServerError
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }
}
