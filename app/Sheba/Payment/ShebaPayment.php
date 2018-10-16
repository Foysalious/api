<?php


namespace Sheba\Payment;

use App\Models\PartnerOrderPayment;
use App\Models\Payable;
use App\Models\Payment;
use Cache;
use Carbon\Carbon;
use Sheba\Payment\Complete\PaymentComplete;
use Sheba\Payment\Factory\PaymentProcessor;

class ShebaPayment
{
    private $method;
    private $message;

    public function __construct($enum)
    {
        $this->method = (new PaymentProcessor($enum))->method();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function init(Payable $payable)
    {
        return $this->method->init($payable);
    }

    public function complete(Payment $payment)
    {
        $payment = $this->method->validate($payment);
        if ($payment->status != 'validation_failed') {
            /** @var Payable $payable */
            $payable = $payment->payable;
            $completion_class = $payable->getCompletionClass();
            $completion_class->setPayment($payment);
            $payment = $completion_class->complete();
        }
        return $payment;
    }

    public function isComplete(PayCharged $pay_charged)
    {
        if ($pay_charged->type == 'recharge') {
            $transactions = $pay_charged->user->transactions()->orderBy('id', 'desc')->get();
            foreach ($transactions as $transaction) {
                if ($transaction->transaction_details) {
                    $details = json_decode($transaction->transaction_details);
                    if ($details && isset($details->transaction_id)) {
                        if ($details->transaction_id == $pay_charged->transactionId) {
                            $pay_charged->amount = $transaction->amount;
                            return $pay_charged;
                        }
                    }
                }
            }
        } elseif ($pay_charged->type == 'order') {
            $partner_order_payment = PartnerOrderPayment::where('partner_order_id', $pay_charged->id)->first();
            if ($partner_order_payment) {
                $transaction_id = json_decode($partner_order_payment->transaction_detail)->transaction_id;
                if ($transaction_id == $pay_charged->transactionId) {
                    $pay_charged->amount = $partner_order_payment->amount;
                    return $pay_charged;
                }
            }
        }
        return false;
    }

    public function isCompleteByMethods(PayCharged $pay_charged)
    {
        $paycharge = Cache::store('redis')->get("paycharge::$pay_charged->transactionId");
        if ($paycharge) {
            $paycharge = json_decode($paycharge);
            if (isset($paycharge->isPayChargeSuccess) && $paycharge->isPayChargeSuccess) {
                $pay_chargable = unserialize($paycharge->pay_chargable);
                $pay_charged->amount = $pay_chargable->amount;
                return $pay_charged;
            };
        }
        return false;
    }

    private function updateTransactionRedis($redis_key, $paycharge, $response)
    {
        $paycharge->isPayChargeSuccess = 1;
        $paycharge->payChargeResponse = $response;
        Cache::store('redis')->forget("paycharge::$redis_key");
        Cache::store('redis')->put("paycharge::$redis_key", json_encode($paycharge), Carbon::tomorrow());
        return $paycharge;
    }
}