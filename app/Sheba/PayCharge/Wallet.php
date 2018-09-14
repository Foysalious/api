<?php


namespace Sheba\PayCharge;

use App\Models\CustomerTransaction;
use Carbon\Carbon;
use DB;

trait Wallet
{
    public function rechargeWallet($amount, $transaction_data)
    {
        DB::transaction(function () use ($amount, $transaction_data) {
            $this->creditWallet($amount);
            $this->walletTransaction($transaction_data);
        });
    }

    public function creditWallet($amount)
    {
        $this->wallet += $amount;
        $this->update();
    }

    public function debitWallet($amount)
    {
        $this->wallet -= $amount;
        $this->update();
    }

    public function walletTransaction($transaction_data)
    {
        $this->transactions()->save(new CustomerTransaction(array_merge($transaction_data, ['created_at' => Carbon::now()])));
    }


}