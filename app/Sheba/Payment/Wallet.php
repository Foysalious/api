<?php namespace Sheba\Payment;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Partner;
use App\Models\PartnerTransaction;
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
        } else if ($this instanceof Partner) {
            return new PartnerTransaction();
        }
    }
}