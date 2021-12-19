<?php

namespace Sheba\AccountingEntry\Repository;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class AccountingEntryClient
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;
    protected $userType;
    protected $userId;
    protected $reportType;
    const RETRY          = 5;
    const RETRY_DURATION = 2000;

    public function __construct(Client $client)
    {
        $this->client  = $client;
        $this->baseUrl = rtrim(config('accounting_entry.api_url'), '/');
        $this->apiKey  = config('accounting_entry.api_key');
    }

    /**
     * @param $uri
     * @param null $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function get($uri, $data = null)
    {
        return $this->call('get', $uri, $data);
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
     * @param $uri
     * @param $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }

    /**
     * @param $uri
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function delete($uri)
    {
        return $this->call('delete', $uri);
    }


    /**
     * @param      $method
     * @param      $uri
     * @param null $data
     * @return mixed
     * @throws AccountingEntryServerError
     */
    public function call($method, $uri, $data = null)
    {
        try {
            if (!$this->userType || !$this->userId) {
                throw new AccountingEntryServerError('Set user type and user id', 400);
            }

            $handlerStack = HandlerStack::create(new CurlHandler());
            $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));
            $this->client = new Client(array('handler' => $handlerStack));
            $res          = decodeGuzzleResponse(
                $this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data))
            );
            if ($res['code'] != 200) {
                throw new AccountingEntryServerError($res['message']);
            }
            return $res['data'] ?? $res['message'];

        } catch (GuzzleException $e) {
            $response = $e->getResponse() ? json_decode($e->getResponse()->getBody()->getContents(), true) : null;
            $message  = $e->getMessage();
            if (isset($response['message'])) {
                $message = $response['message'];
            } else if (isset($response['detail'])) {
                $message = json_encode($response['detail']);
            }
            throw new AccountingEntryServerError($message, $e->getCode() ?: 500);
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
    public function getOptions($data = null): array
    {
        $options['headers'] = [
            'Content-Type' => 'application/json',
            'x-api-key'    => $this->apiKey,
            'Accept'       => 'application/json',
            'Ref-Id'       => $this->userId,
            'Ref-Type'     => $this->userType
        ];
        if ($data) {
            $options['json'] = $data;
        }
        return $options;
    }


    /**
     * @param $userType
     * @return $this
     */
    public function setUserType($userType): AccountingEntryClient
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

    private function retryDecider(): Closure
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) {
            // Limit the number of retries to 5
            if ($retries >= self::RETRY) {
                return false;
            }

            // Retry connection exceptions
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // Retry on server errors
                if ($response->getStatusCode() > 500) {
                    return true;
                }
            }

            return false;
        };
    }

    private function retryDelay(): Closure
    {
        return function ($numberOfRetries) {
            return self::RETRY_DURATION * $numberOfRetries;
        };
    }
}
