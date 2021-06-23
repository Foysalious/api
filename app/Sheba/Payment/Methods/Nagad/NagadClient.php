<?php namespace Sheba\Payment\Methods\Nagad;


use Sheba\Payment\Methods\Nagad\Response\CheckoutComplete;
use Sheba\Payment\Methods\Nagad\Response\Initialize;
use Sheba\Payment\Methods\Nagad\Stores\NagadStore;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPRequest;

class NagadClient
{
    private $client;
    private $baseUrl;
    /**
     * @var NagadStore
     */
    private $store;
    const TIMEOUT = 120;

    public function __construct(TPProxyClient $client)
    {
        $this->client = $client;

    }

    public function setStore(NagadStore $store)
    {
        $this->store   = $store;
        $this->baseUrl = $this->store->getBaseUrl();
        return $this;
    }

    /**
     * @param $transactionId
     * @return Initialize
     * @throws Exception\EncryptionFailed
     * @throws TPProxyServerError
     */
    public function init($transactionId)
    {
        ini_set('max_execution_time', self::TIMEOUT + self::TIMEOUT);
        $merchantId = $this->store->getMerchantId();
        $url        = "$this->baseUrl/api/dfs/check-out/initialize/$merchantId/$transactionId";
        $data       = Inputs::init($transactionId, $this->store);
        $request    = (new TPRequest())->setMethod(TPRequest::METHOD_POST)->setHeaders(Inputs::headers())->setInput($data)->setUrl($url)->setTimeout(self::TIMEOUT);
        $resp       = $this->client->call($request);
        return new Initialize($resp, $this->store);
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
        $data         = Inputs::complete($transactionId, $resp, $amount, $callbackUrl, $this->store);
        $request      = (new TPRequest())->setUrl($url)->setMethod(TPRequest::METHOD_POST)->setHeaders(Inputs::headers())->setInput($data)->setTimeout(self::TIMEOUT);
        $resp         = $this->client->call($request);
        return new CheckoutComplete($resp, $this->store);
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
        $request = (new TPRequest())->setUrl($url)->setMethod(TPRequest::METHOD_GET)->setHeaders(Inputs::headers())->setTimeout(self::TIMEOUT);
        $resp    = $this->client->call($request);
        return new Validator($resp, true);

    }
}
