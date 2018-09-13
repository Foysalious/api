<?php


namespace Sheba\PayCharge;

use DB;

trait RechargeWallet
{
    public function rechargeWallet($amount, $transaction_data)
    {
        DB::transaction(function () use ($amount, $transaction_data) {
            $this->creditWallet($amount);
            $this->walletTransaction($transaction_data);
        });

    }

}