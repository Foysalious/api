<?php namespace Sheba\AccountingEntry\Repository;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class AccountingEntryClient
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('accounting_entry.api_url'), '/');
        $this->apiKey = config('accounting_entry.api_key');
    }

    /**
     * @param $uri
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function call($method, $uri, $data = null)
    {
        try {
            $res = decodeGuzzleResponse($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));
            if ($res['code'] != 200) throw new AccountingEntryServerError($res['message']);
            unset($res['code'], $res['code']);
            return $res;
        } catch (GuzzleException $e) {
            $res = decodeGuzzleResponse($e->getResponse());
            if ($res['code'] == 400) throw new AccountingEntryServerError($res['message']);
            throw new AccountingEntryServerError($e->getMessage());
        }
    }

    /**
     * @param $uri
     * @return string
     */
    public function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    /**
     * @param null $data
     * @return array
     */
    public function getOptions($data = null)
    {
        $options['headers'] = ['Content-Type' => 'application/json', 'x-api-key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($data) {
            $options['json'] = $data;
        }
        return $options;
    }
}