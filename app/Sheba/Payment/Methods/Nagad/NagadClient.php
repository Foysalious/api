<?php namespace Sheba\Payment\Methods\Nagad;


use Sheba\Payment\Methods\Nagad\Response\CheckoutComplete;
use Sheba\Payment\Methods\Nagad\Response\Initialize;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPRequest;

class NagadClient
{
    private $client;
    private $baseUrl;
    private $merchantId;
    private $publicKey;
    private $privateKey;
    private $contextPath;

    public function __construct(NagadHttpClient $client)
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
     * @throws TPProxyServerError|Exception\EncryptionFailed
     */
    public function init($transactionId)
    {
        $url     = "$this->baseUrl/api/dfs/check-out/initialize/$this->merchantId/$transactionId";
        $data    = Inputs::init($transactionId);
        $request = (new TPRequest())->setMethod(TPRequest::METHOD_POST)->setHeaders(Inputs::headers())->setInput($data)->setUrl($url);
        $resp    = $this->client->call($request);
        return new Initialize($resp);
    }

    /**
     * @param            $transactionId
     * @param Initialize $resp
     * @param            $amount
     * @param            $callbackUrl
     * @return CheckoutComplete
     * @throws Exception\EncryptionFailed
     * @throws TPProxyServerError
     */
    public function placeOrder($transactionId, Initialize $resp, $amount, $callbackUrl)
    {
        $paymentRefId = $resp->getPaymentReferenceId();
        $url          = "$this->baseUrl/api/dfs/check-out/complete/$paymentRefId";
        $data         = Inputs::complete($transactionId, $resp, $amount, $callbackUrl);
        $request      = (new TPRequest())->setUrl($url)->setMethod(TPRequest::METHOD_POST)->setHeaders(Inputs::headers())->setInput($data);
        $resp         = $this->client->call($request);
        return new CheckoutComplete($resp);
    }

    /**
     * @param $refId
     * @return Validator
     * @throws Exception\InvalidOrderId
     * @throws TPProxyServerError
     */
    public function validate($refId)
    {
        $url     = "$this->baseUrl/api/dfs/verify/payment/$refId";
        $request = (new TPRequest())->setUrl($url)->setMethod(TPRequest::METHOD_GET)->setHeaders(Inputs::headers());
        $resp    = $this->client->call($request);
        return new Validator($resp, true);

    }
}
