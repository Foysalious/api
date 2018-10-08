<?php

namespace Sheba\PayCharge\Methods;


use Carbon\Carbon;
use Sheba\PayCharge\Adapters\Error\WalletErrorAdapter;
use Sheba\PayCharge\PayChargable;
use Cache;

class Wallet implements PayChargeMethod
{
    private $error;

    public function init(PayChargable $payChargable)
    {
        $invoice = "SHEBA_WALLET_" . strtoupper($payChargable->type) . '_' . $payChargable->id . '_' . Carbon::now()->timestamp;
        $payment_info = array(
            'transaction_id' => $invoice,
            'id' => $payChargable->id,
            'type' => $payChargable->type,
            'pay_chargable' => serialize($payChargable),
            'link' => '',
            'method_info' => array('transaction_id' => $invoice),
        );
        Cache::store('redis')->put("paycharge::$invoice", json_encode($payment_info), Carbon::tomorrow());
        array_forget($payment_info, 'pay_chargable');
        array_forget($payment_info, 'method_info');
        return $payment_info;
    }

    public function validate($payment)
    {
        try {
            $pay_chargable = unserialize($payment->pay_chargable);
            $class_name = $pay_chargable->userType;
            $user = $class_name::find($pay_chargable->userId);
            $transaction = $user->transactions()->where('partner_order_id', $pay_chargable->id)->first();
            if ($transaction && $transaction->amount == $pay_chargable->amount && (json_decode($transaction->transaction_details))->transaction_id == $payment->transaction_id) {
                return $payment->method_info;
            } else {
                return null;
            }
        } catch ( \Throwable $e ) {
            return null;
        }
    }

    public function formatTransactionData($method_response)
    {
        return array(
            'name' => 'Wallet',
            'details' => array(
                'transaction_id' => $method_response->transaction_id,
                'gateway' => '',
                'details' => ''
            )
        );
    }

    public function getError(): PayChargeMethodError
    {
        return (new WalletErrorAdapter($this->error))->getError();
    }
}