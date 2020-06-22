<?php namespace Sheba\Payment;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use App\Models\Business;
use App\Models\BusinessTransaction;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Resource;
use App\Models\Vendor;
use App\Models\VendorTransaction;
use Carbon\Carbon;
use DB;
use Sheba\Dal\ResourceTransaction\Model;

trait Wallet
{
    public function rechargeWallet($amount, $transaction_data)
    {
        /** @var PartnerTransaction $transaction */
        $transaction = null;
        DB::transaction(function () use ($amount, $transaction_data, &$transaction) {
            $this->creditWallet($amount);
            $transaction_data = array_merge($transaction_data, ['amount' => $amount, 'type' => 'Credit']);
            $transaction = $this->walletTransaction($transaction_data);
        });
        return $transaction;
    }

    public function minusWallet($amount, $transaction_data)
    {
        /** @var PartnerTransaction $transaction */
        $transaction = null;
        DB::transaction(function () use ($amount, $transaction_data, &$transaction) {
            $this->debitWallet($amount);
            $transaction_data = array_merge($transaction_data, ['amount' => $amount, 'type' => 'Debit']);
            $transaction = $this->walletTransaction($transaction_data);
        });
        return $transaction;
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
        return $this->transactions()->save($user_transaction);
    }

    private function getUserTransaction()
    {
        if ($this instanceof Customer) {
            return new CustomerTransaction();
        } else if ($this instanceof Affiliate) {
            return new AffiliateTransaction();
        } else if ($this instanceof Partner) {
            return new PartnerTransaction();
        } else if ($this instanceof Vendor) {
            return new VendorTransaction();
        } else if ($this instanceof Business) {
            return new BusinessTransaction();
        } else if ($this instanceof Resource) {
            return new Model();
        }
    }
}