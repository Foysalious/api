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
    /**@var NagadStore $store */
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
     * @throws TPProxyServerError
     */
    public function init($transaction_id): Initialize
    {
        $merchantId = $this->store->getMerchantId();
        $url = "$this->baseUrl/api/dfs/check-out/initialize/$merchantId/$transaction_id";
        list($payment_data, $store_data) = Inputs::init($transaction_id, $this->store);

        $request = (new NagadRequest())
            ->setUrl($url)
            ->setMethod(NagadRequest::METHOD_POST)
            ->setHeaders(Inputs::headers())
            ->setInput($payment_data)
            ->setStoreData($store_data);

        $response = $this->client->call($request);

        return new Initialize($response, $this->store);
    }

    /**
     * @param $transaction_id
     * @param Initialize $resp
     * @param $amount
     * @param $call_back_url
     * @return CheckoutComplete
     * @throws TPProxyServerError
     */
    public function placeOrder($transaction_id, Initialize $resp, $amount, $call_back_url): CheckoutComplete
    {
        ini_set('max_execution_time', self::TIMEOUT + self::TIMEOUT);
        $payment_ref_id = $resp->getPaymentReferenceId();
        $url = "$this->baseUrl/api/dfs/check-out/complete/$payment_ref_id";
        list($payment_data, $store_data) = Inputs::complete($transaction_id, $resp, $amount, $call_back_url, $this->store);

        $request = (new NagadRequest())
            ->setUrl($url)
            ->setMethod(NagadRequest::METHOD_POST)
            ->setHeaders(Inputs::headers())
            ->setInput($payment_data)
            ->setStoreData($store_data);

        $resp = $this->client->call($request);
        return new CheckoutComplete($resp, $this->store);
    }

    /**
     * @param $ref_id
     * @return Validator
     * @throws Exception\InvalidOrderId
     * @throws TPProxyServerError
     */
    public function validate($ref_id): Validator
    {
        ini_set('max_execution_time', self::TIMEOUT + self::TIMEOUT);
        $url = "$this->baseUrl/api/dfs/verify/payment/$ref_id";
        $request = (new NagadRequest())
            ->setUrl($url)
            ->setMethod(NagadRequest::METHOD_GET)
            ->setHeaders(Inputs::headers());

        $resp = $this->client->call($request);
        return new Validator($resp, true);
    }
}
