<?php

namespace Sheba\Payment\Methods\OkWallet;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Sheba\Payment\Methods\OkWallet\Request\InitRequest;
use App\Sheba\Payment\Methods\OkWallet\Response\ValidateTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Payment\Methods\OkWallet\Exception\FailedToInitiateException;
use Sheba\Payment\Methods\OkWallet\Response\ValidationResponse;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Statuses;
use Sheba\RequestIdentification;

class OkWallet extends PaymentMethod
{
    const NAME = 'ok_wallet';

    public function __construct()
    {
        parent::__construct();
        $this->successUrl = '';
        $this->failUrl    = '';

    }

    /**
     * @param Payable $payable
     * @return Payment
     * @throws Exception\FailedToInitiateException
     * @throws Exception\KeyEncryptionFailed
     */
    public function init(Payable $payable): Payment
    {
        $invoice = "SHEBA_OK_WALLET_" . strtoupper($payable->readable_type) . '_' . $payable->type_id . '_' . randomString(10, 1, 1);
        $user    = $payable->user;
        $payment = new Payment();
        DB::transaction(function () use ($payment, $payable, $invoice, $user) {
            $payment->payable_id             = $payable->id;
            $payment->transaction_id         = $invoice;
            $payment->status                 = Statuses::INITIATED;
            $payment->valid_till             = Carbon::now()->addMinutes(30);
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
        $session = (new OkWalletClient())->createSession($payment->payable->amount, $payment->getShebaTransaction()->getTransactionId());
        if ($session->hasError()) {
            $payment->transaction_details = $session->toString();
            $payment->save();
            throw new FailedToInitiateException($session->getMessage());
        }
        $payment->gateway_transaction_id = $session->getSessionKey();
        $payment->transaction_details = $session->toString();
        $payment->redirect_url        = $session->getRedirectUrl();
        $payment->update();
        return $payment;
    }

    /**
     * @param Payment $payment
     * @return Payment
     */
    public function validate(Payment $payment)
    {
        $request = request()->all();

        $request = (new InitRequest(json_decode($request['data'],true)));

        $validate_transaction = (new ValidateTransaction($this->paymentRepository))->setPayment($payment);

        if ($request->getRescode() != 2000) {
            $payment = $validate_transaction->changeToFailed();

        } else {
            $payment = $validate_transaction->initValidation();
        }
        $payment->update();
        return $payment;
    }


}
