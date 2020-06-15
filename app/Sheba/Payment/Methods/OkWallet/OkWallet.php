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
        $invoice = "SHEBA_OK_WALLET_" . strtoupper($payable->readable_type) . '_' . $payable->type_id . '_' . randomString(10, 1, 1);
        $user    = $payable->user;
        $payment = new Payment();
        DB::transaction(function () use (&$payment, $payable, $invoice, $user) {
            $payment->payable_id     = $payable->id;
            $payment->transaction_id = $invoice;
            $payment->status         = Statuses::INITIATED;
            $payment->valid_till     = $this->getValidTill();
            $this->setModifier($user);
            $payment->fill((new RequestIdentification())->get());
            $this->withCreateModificationField($payment);
            $payment->save();
            $payment_details             = new PaymentDetail();
            $payment_details->payment_id = $payment->id;
            $payment_details->method     = self::NAME;
            $payment_details->amount     = $payable->amount;
            $payment_details->save();
        });
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
     * @throws \Sheba\TPProxy\TPProxyServerError
     */
    public function validate(Payment $payment)
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
