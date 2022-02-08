<?php

namespace Sheba\Payment\Methods\Upay;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\Payment\Methods\Upay\Response\UpayApiResponse;
use Sheba\Payment\Methods\Upay\Stores\UpayStore;

class UpayClient
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var string
     */
    private $baseUrl;
    const TIMEOUT = 120;
    /** @var array */
    private $headers = [];
    private $url     = '';
    private $method  = 'POST';
    private $payload = [];

    public function __construct()
    {
        $this->client  = new Client();
        $this->baseUrl = config('payment.upay.base_url');
    }

    public function call()
    {
        try {
            $res = $this->client->request($this->method, $this->getUrl(), [
                'headers'         => $this->headers,
                'form_params'     => $this->payload,
                'timeout'         => self::TIMEOUT,
                'read_timeout'    => self::TIMEOUT,
                'connect_timeout' => self::TIMEOUT,
                'http_errors'     => false
            ])->getBody()->getContents();
            return (new UpayApiResponse())->setServerResponse($res);
        } catch (GuzzleException $e) {
            $server_response = json_encode(['code' => $e->getCode(), 'message' => $e->getMessage()]);
            return (new UpayApiResponse())->setServerResponse($server_response);
        }
    }

    public function getUrl()
    {
        return "$this->baseUrl/$this->url";
    }

    /**
     * @param array $headers
     * @return UpayClient
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param string $url
     * @return UpayClient
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string $method
     * @return UpayClient
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param array $payload
     * @return UpayClient
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }


}
