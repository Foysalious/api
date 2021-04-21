<?php namespace Sheba\AccountingEntry\Repository;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class AccountingEntryClient
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;
    protected $userType;
    protected $userId;

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
            if (!$this->userType || !$this->userId) {
                return "set userType and userId";
            }
            $res = decodeGuzzleResponse($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));
            if ($res['code'] != 200) throw new AccountingEntryServerError($res['message']);
            unset($res['code'], $res['message']);
            return $res['data'];
        } catch (GuzzleException $e) {
            $res = decodeGuzzleResponse($e->getResponse());
            throw new AccountingEntryServerError($res['message'], $res['code']);
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
        $options['headers'] = ['Content-Type' => 'application/json', 'x-api-key' => $this->apiKey,
            'Accept' => 'application/json','Ref-Id' => $this->userId, 'Ref-Type' => $this->userType];
        if ($data) {
            $options['json'] = $data;
        }
        return $options;
    }


    /**
     * @param $userType
     * @return $this
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;
        return $this;
    }


    /**
     * @param $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
}