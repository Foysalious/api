<?php


namespace App\Sheba;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;

abstract class APIClient
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('expense_tracker.api_url'), '/');
        $this->apiKey = config('expense_tracker.api_key');
    }

    public function setBaseUrl($url): APIClient
    {
        $this->baseUrl = $url;
        return $this;
    }

    public function setApiKey($apiKey): APIClient
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @param $uri
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    private function call($method, $uri, $data = null)
    {
        try {
            $res = decodeGuzzleResponse($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));
            if ($res['code'] != 200)
                throw new ExpenseTrackingServerError($res['message']);
            unset($res['code'], $res['message']);
            return $res;
        } catch (GuzzleException $e) {
            $res = decodeGuzzleResponse($e->getResponse());
            if ($res['code'] == 400)
                throw new ExpenseTrackingServerError($res['message']);
            throw new ExpenseTrackingServerError($e->getMessage());
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
            'x-api-key'    => $this->apiKey,
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
     * @throws ExpenseTrackingServerError
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }

    /**
     * @param $uri
     * @return mixed
     * @throws ExpenseTrackingServerError
     */
    public function delete($uri)
    {
        return $this->call('DELETE', $uri);
    }
}