<?php namespace Sheba\Payment\Methods\OkWallet;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Sheba\Payment\Methods\OkWallet\Request\InitRequest;
use App\Sheba\Payment\Methods\OkWallet\Response\ValidateTransaction;
use Illuminate\Support\Facades\DB;
use Sheba\Payment\Methods\OkWallet\Exception\FailedToInitiateException;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Statuses;
use Sheba\RequestIdentification;
use Sheba\TPProxy\TPProxyServerError;

class OkWallet extends PaymentMethod
{
    const NAME = 'ok_wallet';

    /**
     * @param Payable $payable
     * @return Payment
     * @throws \Throwable
     */
    public function init(Payable $payable): Payment
    {
        $payment = $this->createPayment($payable);
        try {
            /** @var OkWalletClient $ok_wallet */
            $ok_wallet = app(OkWalletClient::class);
            $session = $ok_wallet->createSession($payment->payable->amount, $payment->getShebaTransaction()->getTransactionId());
        } catch (\Throwable $e) {
            $error = ['status' => "failed", "errorMessage" => $e->getMessage(), 'statusCode' => $e->getCode()];
            $this->onInitFailed($payment, json_encode($error));
            throw $e;
        }
        if ($session->hasError()) {
            $this->onInitFailed($payment, $session->toString());
            throw new FailedToInitiateException($session->getMessage());
        }
        $payment->gateway_transaction_id = $session->getSessionKey();
        $payment->transaction_details    = $session->toString();
        $payment->redirect_url           = $session->getRedirectUrl();
        $payment->update();
        return $payment;

    }

    private function onInitFailed(Payment $payment, $error)
    {
        $this->paymentLogRepo->setPayment($payment);
        $this->paymentLogRepo->create([
            'to'                  => Statuses::INITIATION_FAILED,
            'from'                => $payment->status,
            'transaction_details' => $error
        ]);
        $payment->status              = Statuses::INITIATION_FAILED;
        $payment->transaction_details = $error;
        $payment->update();
    }

    /**
     * @param Payment $payment
     * @return Payment
     * @throws TPProxyServerError
     */
    public function validate(Payment $payment): Payment
    {
        $request = request()->all();
        $request = (new InitRequest(json_decode($request['data'], true)));
        $validate_transaction = (new ValidateTransaction($this->paymentLogRepo))->setPayment($payment);

        if ($request->getRescode() != 2000) {
            $payment = $validate_transaction->changeToFailed();
        } else {
            $payment = $validate_transaction->initValidation();
        }
        $payment->update();
        return $payment;
    }

    public function getMethodName()
    {
        return self::NAME;
    }
}
