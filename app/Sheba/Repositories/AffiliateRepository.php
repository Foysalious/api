<?php namespace Sheba\Repositories;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;

class AffiliateRepository extends  BaseRepository
{
    public function creditWallet(Affiliate $affiliate, $amount, $log_data)
    {
        $affiliate->update(['wallet' => $affiliate->wallet + $amount]);
        $this->walletTransaction($affiliate, $log_data);
    }

    public function debitWallet(Affiliate $affiliate, $amount, $log_data)
    {
        $affiliate->update(['wallet' => $affiliate->wallet - $amount]);
        $this->walletTransaction($affiliate, $log_data);
    }

    public function walletTransaction(Affiliate $affiliate, $data)
    {
        $affiliate->transactions()->save(new AffiliateTransaction($this->withCreateModificationField($data)));
    }
}