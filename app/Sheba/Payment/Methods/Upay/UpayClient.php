<?php

namespace Sheba\Payment\Methods\Upay;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\Payment\Methods\Upay\Response\UpayApiResponse;
use Sheba\Payment\Methods\Upay\Stores\UpayStore;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPRequest;

class UpayClient
{
    /** @var TPProxyClient $client */
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
        $this->client  = app(TPProxyClient::class);
        $this->baseUrl = config('payment.upay.base_url');
        $this->headers= ["Content-Type:application/json"];
    }

    /**
     * @return UpayApiResponse
     */
    public function call(): UpayApiResponse
    {
        $methods=['GET'=>TPRequest::METHOD_GET,'POST'=>TPRequest::METHOD_POST];
        try {
            $request=new TPRequest();
            $request->setHeaders($this->headers)->setMethod($methods[$this->method])->setInput($this->payload)->setTimeout(self::TIMEOUT)->setUrl($this->getUrl());
            $res = $this->client->call($request);
            return (new UpayApiResponse())->setServerResponse($res);
        } catch (TPProxyServerError $e) {
            $server_response = json_encode(['code' => $e->getCode(), 'message' => $e->getMessage()]);
            return (new UpayApiResponse())->setServerResponse($server_response);
        }
    }

    public function getUrl(): string
    {
        return "$this->baseUrl/$this->url";
    }

    /**
     * @param array $headers
     * @return UpayClient
     */
    public function setHeaders(array $headers): UpayClient
    {
        $this->headers = array_merge($this->headers,$headers);
        return $this;
    }

    /**
     * @param string $url
     * @return UpayClient
     */
    public function setUrl(string $url): UpayClient
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string $method
     * @return UpayClient
     */
    public function setMethod(string $method): UpayClient
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param array $payload
     * @return UpayClient
     */
    public function setPayload(array $payload): UpayClient
    {
        $this->payload = $payload;
        return $this;
    }


}
