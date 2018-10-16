<?php


namespace Sheba\Payment;

use App\Models\PartnerOrderPayment;
use Cache;
use Carbon\Carbon;
use Sheba\Payment\Complete\PayChargeComplete;
use Sheba\Payment\Factory\PaymentProcessor;

class Payment
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

    public function init(PayChargable $payChargable)
    {
        return $this->method->init($payChargable);
    }

    public function complete($redis_key)
    {
        $paycharge = Cache::store('redis')->get("paycharge::$redis_key");
        $paycharge = json_decode($paycharge);
        if ($response = $this->method->validate($paycharge)) {
            $pay_chargable = unserialize($paycharge->pay_chargable);
            $this->updateTransactionRedis($redis_key, $paycharge, $response);
            $class_name = "Sheba\\Payment\\Complete\\" . $pay_chargable->completionClass;
            /** @var PayChargeComplete $complete_class */
            $complete_class = new $class_name();
            if ($complete_class->complete($pay_chargable, $this->method->formatTransactionData($response))) {
                Cache::store('redis')->forget("paycharge::$redis_key");
                return array('redirect_url' => $pay_chargable->redirectUrl);
            }
        } else {
            $error = $this->method->getError();
            $this->message = 'Sorry, your ' . ucwords($paycharge->type) . ' payment has failed. Due to ' . $error->message;
            return false;
        }
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