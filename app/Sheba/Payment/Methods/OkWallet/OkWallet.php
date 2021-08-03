<?php namespace Sheba\Payment\Methods\OkWallet;

use App\Models\Payable;
use App\Models\Payment;
use Sheba\Payment\Methods\Nagad\Exception\InvalidOrderId;
use Sheba\Payment\Methods\Nagad\Validator;
use Sheba\Payment\Methods\OkWallet\Exception\FailedToInitiateException;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Statuses;
use Throwable;

class OkWallet extends PaymentMethod
{
    const NAME = 'ok_wallet';
    /** @var OkWalletClient $client */
    private $client;

    /**
     * OkWallet constructor.
     * @param OkWalletClient $okwallet_client
     */
    public function __construct(OkWalletClient $okwallet_client)
    {
        parent::__construct();
        $this->client = $okwallet_client;
    }

    /**
     * @param Payable $payable
     * @return Payment
     * @throws Throwable
     */
    public function init(Payable $payable): Payment
    {
        $payment = $this->createPayment($payable);
        try {
            $order_create = $this->client
                ->createOrder($payment->payable->amount, $payment->getShebaTransaction()->getTransactionId());
        } catch (Throwable $e) {
            $error = ['status' => "failed", "errorMessage" => $e->getMessage(), 'statusCode' => $e->getCode()];
            $this->onInitFailed($payment, json_encode($error));
            throw $e;
        }

        if ($order_create->hasError()) {
            $this->onInitFailed($payment, $order_create->toString());
            throw new FailedToInitiateException($order_create->getMessage());
        }

        $payment->gateway_transaction_id = $order_create->getOrderId();
        $payment->transaction_details = $order_create->toString();
        $payment->redirect_url = $order_create->getRedirectUrl();

        $payment->update();

        return $payment;
    }

    /**
     * @param Payment $payment
     * @param $error
     */
    private function onInitFailed(Payment $payment, $error)
    {
        $this->paymentLogRepo->setPayment($payment);
        $this->paymentLogRepo->create([
            'to' => Statuses::INITIATION_FAILED,
            'from' => $payment->status,
            'transaction_details' => $error
        ]);
        $payment->status = Statuses::INITIATION_FAILED;
        $payment->transaction_details = $error;
        $payment->update();
    }

    /**
     * @param Payment $payment
     * @return Payment
     * @throws FailedToInitiateException
     */
    public function validate(Payment $payment): Payment
    {
        $verify_order = $this->client->validateOrder($payment);
        if ($verify_order->hasError()) {
            $this->statusChanger->setPayment($payment)->changeToValidationFailed($verify_order->toString());
        } else {
            $this->statusChanger->setPayment($payment)->changeToValidated($verify_order->toString());
        }

        return $payment;
    }

    public function getMethodName(): string
    {
        return self::NAME;
    }
}
