<?php


namespace Sheba\PayCharge;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use App\Models\Customer;
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
        $bonuses = $this->bonuses()->where('valid_till', '>=', Carbon::now())->orderBy('valid_till', 'asc')->get();
        if($bonuses->count()>0)
        $this->wallet -= $amount;
        $this->update();
    }

    public function walletTransaction($transaction_data)
    {
        $data = array_merge($transaction_data, ['created_at' => Carbon::now()]);
        $user_transaction = $this->getUserTransaction()->fill($data);
        $this->transactions()->save($user_transaction);
    }

    private function getUserTransaction()
    {
        if ($this instanceof Customer) {
            return new CustomerTransaction();
        } else if ($this instanceof Affiliate) {
            return new AffiliateTransaction();
        }
    }

}