<?php namespace Sheba\Payment\Methods\Nagad;

use Sheba\Payment\Methods\Nagad\Response\CheckoutComplete;
use Sheba\Payment\Methods\Nagad\Response\Initialize;
use Sheba\Payment\Methods\Nagad\Stores\NagadStore;
use Sheba\TPProxy\NagadProxyClient;
use Sheba\TPProxy\NagadRequest;
use Sheba\TPProxy\TPProxyServerError;

class NagadClient
{
    private $client;
    private $baseUrl;
    /**
     * @var NagadStore
     */
    private $store;

    /**
     * NagadClient constructor.
     * @param \Sheba\TPProxy\TPProxyClient $client
     */
    public function __construct(NagadProxyClient $client)
    {
        $this->client = $client;
    }

    public function setStore(NagadStore $store)
    {
        $this->store = $store;
        $this->baseUrl = $this->store->getBaseUrl();
        return $this;
    }

    /**
     * @param $transactionId
     * @return Initialize
     * @throws Exception\EncryptionFailed
     * @throws TPProxyServerError
     */
    public function init($transaction_id)
    {
        $merchantId = $this->store->getMerchantId();
        $url = "$this->baseUrl/api/dfs/check-out/initialize/$merchantId/$transaction_id";
        $data = Inputs::init($transaction_id, $this->store);

        $request = (new NagadRequest())
            ->setMethod(NagadRequest::METHOD_POST)
            ->setHeaders(Inputs::headers())
            ->setInput($data)
            ->setUrl($url);


        $resp = $this->client->call($request);

        return new Initialize($resp, $this->store);
    }

    /**
     * @param $transactionId
     * @param Initialize $resp
     * @param $amount
     * @param $callbackUrl
     * @return CheckoutComplete
     * @throws Exception\EncryptionFailed
     * @throws TPProxyServerError
     */
    public function placeOrder($transactionId, Initialize $resp, $amount, $callbackUrl)
    {
        $paymentRefId = $resp->getPaymentReferenceId();
        $url = "$this->baseUrl/api/dfs/check-out/complete/$paymentRefId";
        $data = Inputs::complete($transactionId, $resp, $amount, $callbackUrl, $this->store);
        $request = (new NagadRequest())
            ->setUrl($url)
            ->setMethod(NagadRequest::METHOD_POST)
            ->setHeaders(Inputs::headers())
            ->setInput($data);

        $resp = $this->client->call($request);

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
        $url = "$this->baseUrl/api/dfs/verify/payment/$refId";
        $request = (new NagadRequest())
            ->setUrl($url)
            ->setMethod(NagadRequest::METHOD_GET)
            ->setHeaders(Inputs::headers());

        $resp = $this->client->call($request);

        return new Validator($resp, true);
    }
}
