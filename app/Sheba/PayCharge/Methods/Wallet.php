<?php

namespace Sheba\PayCharge\Methods;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
            if (!$user) throw new ModelNotFoundException("$pay_chargable->userType not found by ID " . $pay_chargable->userId);
            $transaction = $user->transactions()->where('partner_order_id', $pay_chargable->id)->first();
            if (!$transaction) throw new ModelNotFoundException("$pay_chargable->type  not found by " . $pay_chargable->id);
            $transaction_exists = $this->isTransactionIdMatches($payment->transaction_id, $transaction);
            if (!$transaction_exists) throw new ModelNotFoundException("$payment->transaction_id  not found in transaction $payment->transaction_id");
            return $transaction && $transaction_exists ? $payment->method_info : null;
        } catch (\Throwable $e) {
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

    private function isTransactionIdMatches($transaction_id, $transaction)
    {
        return (json_decode($transaction->transaction_details))->transaction_id == $transaction_id;
    }
}