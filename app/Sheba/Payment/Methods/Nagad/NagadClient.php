<?php


namespace Sheba\Payment\Methods\Nagad;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\TPProxy\TPProxyServerError;

class NagadClient
{
    private $client;
    private $baseUrl;
    private $merchantId;
    private $publicKey;
    private $privateKey;
    private $contextPath;

    public function __construct(Client $client)
    {
        $this->client      = $client;
        $this->baseUrl     = config('nagad.base_url');
        $this->merchantId  = config('nagad.merchant_id');
        $this->publicKey   = file_get_contents(config('nagad.public_key_path'));
        $this->privateKey  = file_get_contents(config('nagad.private_key_path'));
        $this->contextPath = config('nagad.context_path');
    }

    /**
     * @param $amount
     * @param $transactionId
     * @return mixed
     * @throws Exception\EncryptionFailed
     * @throws TPProxyServerError
     */
    public function init($transactionId)
    {
        $url     = "$this->baseUrl/$this->contextPath/api/dfs/check-out/initialize/$this->merchantId/$transactionId";
        $data    = Inputs::init($transactionId);
        $request = decodeGuzzleResponse($this->client->request('POST', $url, ['headers' => Inputs::headers(), 'json' => $data, 'http_errors' => false]));
        return $request;
    }
}
