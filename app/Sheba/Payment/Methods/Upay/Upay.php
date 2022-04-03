<?php

namespace Sheba\Payment\Methods\Upay;

use App\Http\Requests\Request;
use App\Models\Payable;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Methods\Upay\Exceptions\UpayApiCallException;
use Sheba\Payment\Methods\Upay\Response\UpayApiResponse;
use Sheba\Payment\Methods\Upay\Response\UpayInitiatePaymentResponse;
use Sheba\Payment\Methods\Upay\Response\UpayLoginResponse;
use Sheba\Payment\Methods\Upay\Response\UpayValidateApiResponse;
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
    const LOGIN_URL    = 'payment/merchant-auth/';
    const INIT_URL     = 'payment/merchant-payment-init/';
    const VALIDATE_URL = 'payment/single-payment-status/';
    const NAME         = 'upay';


    public function setStore(UpayStore $store): Upay
    {
        $this->store  = $store;
        $this->config = $store->getConfig();
        return $this;
    }

    private function setHeader()
    {
        $this->headers = ["Authorization:UPAY $this->login_token"];
    }

    /**
     * @return void
     * @throws UpayApiCallException
     */
    private function login()
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
        $res     = (new UpayClient())->setHeaders($this->headers)->setPayload($payload)->setUrl(self::INIT_URL)->call();
        if ($res->hasError()) {
            return $this->onInitFailed($payment, $res);
        }
        return $this->onSuccess($payment, $res);

    }

    private function onSuccess(Payment $payment, UpayApiResponse $res)
    {
        $payment->redirect_url        = (new UpayInitiatePaymentResponse())->setData($res->getData())->gateway_url;
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

    /**
     * @param Payment $payment
     * @return array
     */
    private function buildInitPayload(Payment $payment)
    {
        return array_merge($this->config->toArray(),
            [
                'date'       => Carbon::today()->format('Y-m-d'),
                'txn_id'     => $payment->gateway_transaction_id,
                'invoice_id' => $payment->gateway_transaction_id,
                'amount'     => (double)$payment->payable->amount
            ]);
    }

    /**
     * @throws UpayApiCallException
     */
    public function validate(Payment $payment): Payment
    {
        $this->login();
        $url = self::VALIDATE_URL . '' . $payment->gateway_transaction_id;
        $this->paymentLogRepo->setPayment($payment);
        $res = (new UpayClient())->setHeaders($this->headers)->setMethod('GET')->setUrl($url)->call();
        if ($res->hasError()) {
            return $this->onValidateFailed($payment, $res);
        }
        $response_data = (new UpayValidateApiResponse())->setData($res->getData());
        if (!$response_data->isSuccess()) return $this->onValidateFailed($payment, $res);
        return $this->onValidated($payment, $res);
    }

    /**
     * @param Payment $payment
     * @param UpayApiResponse $res
     * @return Payment
     */
    private function onValidateFailed(Payment $payment, UpayApiResponse $res)
    {
        $this->paymentLogRepo->create([
            'to'                  => Statuses::VALIDATION_FAILED,
            'from'                => $payment->status,
            'transaction_details' => $payment->transaction_details
        ]);
        $payment->status              = Statuses::VALIDATION_FAILED;
        $payment->transaction_details = $res->toString();
        $payment->update();
        return $payment;
    }

    /**
     * @param Payment $payment
     * @param UpayApiResponse $response
     * @return Payment
     */
    private function onValidated(Payment $payment, UpayApiResponse $response)
    {
        $this->paymentLogRepo->create([
            'to'                  => Statuses::VALIDATED,
            'from'                => $payment->status,
            'transaction_details' => $payment->transaction_details
        ]);
        $payment->status              = Statuses::VALIDATED;
        $payment->transaction_details = $response->toString();
        $payment->update();
        return $payment;
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return self::NAME;
    }
}