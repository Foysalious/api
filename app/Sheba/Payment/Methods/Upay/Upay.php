<?php

namespace Sheba\Payment\Methods\Upay;

use App\Models\Payable;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Methods\Upay\Exceptions\UpayApiCallException;
use Sheba\Payment\Methods\Upay\Response\UpayApiResponse;
use Sheba\Payment\Methods\Upay\Response\UpayInitiatePaymentResponse;
use Sheba\Payment\Methods\Upay\Response\UpayLoginResponse;
use Sheba\Payment\Methods\Upay\Stores\UpayStore;
use Sheba\Payment\Statuses;

class Upay extends PaymentMethod
{
    /**
     * @var UpayStore
     */
    private $store;
    /**
     * @var Stores\UpayStoreConfig
     */
    private $config;
    private $login_token;
    private $headers;
    const LOGIN_URL = 'payment/merchant-auth/';
    const INIT_URL  = 'payment/merchant-payment-init/';
    const NAME      = 'upay';



    public function setStore(UpayStore $store): Upay
    {
        $this->store  = $store;
        $this->config = $store->getConfig();
        return $this;
    }

    private function setHeader()
    {
        $this->headers = ['Authorization' => "UPAY $this->login_token"];
    }

    /**
     * @return void
     * @throws UpayApiCallException
     */
    private function login(): void
    {
        $payload = ['merchant_id' => $this->config->merchant_id, 'merchant_key' => $this->config->merchant_key];
        $res     = (new UpayClient())->setUrl(self::LOGIN_URL)->setPayload($payload)->call();
        if ($res->hasError()) {
            throw new UpayApiCallException("Upay Merchant Login Failed");
        }
        $data              = (new UpayLoginResponse())->setData($res->getData());
        $this->login_token = $data->token;
        $this->setHeader();
    }

    /**
     * @param Payable $payable
     * @return Payment
     * @throws UpayApiCallException
     * @throws Exception
     */
    public function init(Payable $payable): Payment
    {
        $this->login();
        $payment = $this->createPayment($payable, $this->store->getName());
        $payload = $this->buildInitPayload($payment);
        $res = (new UpayClient())->setHeaders($this->headers)->setPayload($payload)->setUrl(self::INIT_URL)->call();
        if ($res->hasError()) {
            return $this->onInitFailed($payment, $res);
        }
        return $this->onSuccess($payment, $res);
        
    }
    private function onSuccess(Payment $payment,UpayApiResponse $res){
        $payment->redirect_url =(new UpayInitiatePaymentResponse())->setData($res->getData())->gateway_url;
        $payment->transaction_details = $res->toString();
        $payment->update();
        return $payment;
    }

    /**
     * @param Payment $payment
     * @param UpayApiResponse $res
     * @return Payment
     */
    private function onInitFailed(Payment $payment, UpayApiResponse $res): Payment
    {
        $this->paymentLogRepo->setPayment($payment);
        $str_resp = $res->toString();
        $this->paymentLogRepo->create([
            'to'                  => Statuses::INITIATION_FAILED,
            'from'                => $payment->status,
            'transaction_details' => $str_resp
        ]);
        $payment->status              = Statuses::INITIATION_FAILED;
        $payment->transaction_details = $str_resp;
        $payment->update();
        return $payment;
    }

    private function buildInitPayload(Payment $payment)
    {
        return array_merge($this->config->toArray(),
            [
                'date'       => Carbon::today()->format('Y-m-d'),
                'txn_id'     => $payment->geteway_transaction_id,
                'invoice_id' => $payment->gateway_transaction_id,
                'amount'     => (double)$payment->payable->amount
            ]);
    }

    public function validate(Payment $payment): Payment
    {
        // TODO: Implement validate() method.
    }

    public function getMethodName(): string
    {
        return self::NAME;
    }
}