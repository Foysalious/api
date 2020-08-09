<?php namespace Sheba\Payment\Methods\Nagad;


use GuzzleHttp\Client;
use Sheba\Payment\Methods\Nagad\Response\CheckoutComplete;
use Sheba\Payment\Methods\Nagad\Response\Initialize;

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
     * @param $transactionId
     * @return Initialize
     * @throws Exception\EncryptionFailed
     */
    public function init($transactionId)
    {
        $url  = "$this->baseUrl/$this->contextPath/api/dfs/check-out/initialize/$this->merchantId/$transactionId";
        $data = Inputs::init($transactionId);
        $resp = decodeGuzzleResponse($this->client->post($url, ['headers' => Inputs::headers(), 'json' => $data, 'http_errors' => false]));
        return new Initialize($resp);
    }

    /**
     * @param            $transactionId
     * @param Initialize $resp
     * @param            $amount
     * @param            $callbackUrl
     * @return CheckoutComplete
     * @throws Exception\EncryptionFailed
     */
    public function placeOrder($transactionId, Initialize $resp, $amount, $callbackUrl)
    {
        $paymentRefId = $resp->getPaymentReferenceId();
        $url          = "$this->baseUrl/$this->contextPath/api/dfs/check-out/complete/$paymentRefId";
        $data         = Inputs::complete($transactionId, $resp, $amount, $callbackUrl);
        $resp         = decodeGuzzleResponse($this->client->post($url, ['headers' => Inputs::headers(), 'json' => $data, 'http_errors' => false]));
        return new CheckoutComplete($resp);
    }

    /**
     * @param $refId
     * @return Validator
     * @throws Exception\InvalidOrderId
     */
    public function validate($refId)
    {
        $url  = "$this->baseUrl/$this->contextPath/api/dfs/verify/payment/$refId";
        $resp = decodeGuzzleResponse($this->client->get($url, ['http_errors' => false]));
        return new Validator($resp,true);

    }
}
