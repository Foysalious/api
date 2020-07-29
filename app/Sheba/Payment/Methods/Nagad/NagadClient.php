<?php


namespace Sheba\Payment\Methods\Nagad;


use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPRequest;

class NagadClient
{
    /** @var TPProxyClient */
    private $tpClient;
    private $baseUrl;
    private $merchantId;
    private $publicKey;
    private $privateKey;
    private $contextPath;

    public function __construct(TPProxyClient $client)
    {
        $this->tpClient    = $client;
        $this->baseUrl     = config('nagad.base_url');
        $this->merchantId  = config('nagad.merchant_id');
        $this->publicKey   = file_get_contents(config('nagad.public_key_path'));
        $this->privateKey  = file_get_contents(config('nagad.private_key_path'));
        $this->contextPath = config('nagad.context_path');
    }

    public function init($amount, $transactionId)
    {
        $url = "$this->baseUrl/$this->contextPath/api/dfs/checkout/initialize/$this->merchantId/$transactionId";
        $request=(new TPRequest())
            ->setMethod(TPRequest::METHOD_POST)
            ->setUrl($url)
            ->setInput();
    }
}
