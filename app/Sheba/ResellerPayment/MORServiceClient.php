<?php

namespace App\Sheba\ResellerPayment;

use App\Exceptions\NotFoundAndDoNotReportException;
use App\Sheba\ResellerPayment\Exceptions\MORServiceServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\ModificationFields;


class MORServiceClient
{
    use ModificationFields;
    protected $client;
    protected $baseUrl;
    private $token;
    private $clientId;
    private $clientSecret;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('reseller_payment.mor.api_url'), '/');
        $this->clientId = config('reseller_payment.mor.client_id');
        $this->clientSecret = config('reseller_payment.mor.client_secret');
    }

    /**
     * @throws NotFoundAndDoNotReportException
     * @throws MORServiceServerError
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
     * @throws NotFoundAndDoNotReportException
     * @throws MORServiceServerError
     */
    private function call($method, $uri, $data = null)
    {
        try {
            $response = $this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data));
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = $res->getBody()->getContents();
            if($http_code == 404) {
                throw new NotFoundAndDoNotReportException($message, $http_code);
            }
            if ($http_code > 399 && $http_code < 500) throw new MORServiceServerError($message, $http_code);
            throw new MORServiceServerError($e->getMessage(), $http_code);
        }
    }

    private function makeUrl($uri): string
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null): array
    {
        $options['headers'] = [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'client-id'     => $this->clientId,
            'client-secret' => $this->clientSecret
        ];
        if ($data) {
            $options['json'] = $data;
        }
        return $options;
    }

    /**
     * @throws NotFoundAndDoNotReportException
     * @throws MORServiceServerError
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }


    /**
     * @param $uri
     * @param $data
     * @return mixed
     * @throws MORServiceServerError
     * @throws NotFoundAndDoNotReportException
     */
    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }

    /**
     * @param $uri
     * @return array|object|string|null
     * @throws MORServiceServerError
     * @throws NotFoundAndDoNotReportException
     */
    public function delete($uri)
    {
        return $this->call('DELETE', $uri);
    }
}
