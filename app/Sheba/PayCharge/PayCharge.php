<?php


namespace Sheba\PayCharge;

use App\Models\PartnerOrderPayment;
use Cache;
USE Redis;

class PayCharge
{
    private $method;
    private $message;

    public function __construct($enum)
    {
        $this->method = (new PayChargeProcessor($enum))->method();
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
            $class_name = "Sheba\\PayCharge\\Complete\\" . $pay_chargable->completionClass;
            $complete_class = new $class_name();
            if ($complete_class->complete($pay_chargable, $this->method->formatTransactionData($response))) {
                Redis::del("paycharge::$redis_key");
                return array('redirect_url' => $pay_chargable->redirectUrl);
            } else {
                $this->message = "Your payment has been successfully received but there was a system error. Call 16516 for support.";
                $sentry = app('sentry');
                $sentry->user_context(['paycharge' => $paycharge, 'transaction' => $response, 'message' => 'Failed to save transaction in DB!']);
                $sentry->captureException(new \Exception('Failed to save transaction in DB!'));
                return false;
            }
        } else {
            $this->message = "Couldn't able to validate.";
            return false;
        }
    }

    public function isComplete(PayCharged $pay_charged)
    {
        if ($pay_charged->type == 'recharge') {
            $transactions = $pay_charged->user->transactions;
            foreach ($transactions as $transaction) {
                if ($transaction->transaction_details) {
                    $transaction_id = json_decode($transaction->transaction_details)->transaction_id;
                    if ($transaction_id == $pay_charged->transactionId) return true;
                }
            }
        } elseif ($pay_charged->type == 'order') {
            $partner_order_payment = PartnerOrderPayment::where('partner_order_id', $pay_charged->id)->first();
            if ($partner_order_payment) {
                $transaction_id = json_decode($partner_order_payment->transaction_detail)->transaction_id;
                if ($transaction_id == $pay_charged->transactionId) return true;
            }
        }
        return false;
    }
}