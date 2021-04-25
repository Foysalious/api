<?php namespace Sheba\Payment\Methods\Nagad;

use Sheba\Payment\Methods\Nagad\Response\CheckoutComplete;
use Sheba\Payment\Methods\Nagad\Response\Initialize;
use Sheba\Payment\Methods\Nagad\Stores\NagadStore;
use Sheba\TPProxy\NagadProxyClient;
use Sheba\TPProxy\NagadRequest;
use Sheba\TPProxy\TPProxyServerError;

class NagadClient
{
    /** @var NagadProxyClient $client */
    private $client;
    private $baseUrl;
    /**
     * @var NagadStore
     */
    private $store;

    /**
     * NagadClient constructor.
     * @param NagadProxyClient $client
     */
    public function __construct(NagadProxyClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param NagadStore $store
     * @return $this
     */
    public function setStore(NagadStore $store): NagadClient
    {
        $this->store = $store;
        $this->baseUrl = $this->store->getBaseUrl();
        return $this;
    }

    /**
     * @param $transaction_id
     * @return Initialize
     * @throws Exception\EncryptionFailed
     * @throws TPProxyServerError
     */
    public function init($transaction_id): Initialize
    {
        $merchantId = $this->store->getMerchantId();
        $url = "$this->baseUrl/api/dfs/check-out/initialize/$merchantId/$transaction_id";
        $data = Inputs::init($transaction_id, $this->store);

        $request = (new NagadRequest())
            ->setMethod(NagadRequest::METHOD_POST)
            ->setHeaders(Inputs::headers())
            ->setInput($data)
            ->setUrl($url);

        $response = $this->client->call($request);

        return new Initialize($response, $this->store);
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
    public function placeOrder($transactionId, Initialize $resp, $amount, $callbackUrl): CheckoutComplete
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
    public function validate($refId): Validator
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
