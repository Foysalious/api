<?php namespace Sheba\Repositories;

use App\Models\Affiliate;
use App\Models\AffiliateTransaction;
use Sheba\Voucher\Creator\BroadcastPromo;

class AffiliateRepository extends BaseRepository
{
    public function creditWallet(Affiliate $affiliate, $amount, $log_data)
    {
        $affiliate->update(['wallet' => $affiliate->wallet + $amount]);
        $this->walletTransaction($affiliate, $log_data);
    }

    public function walletTransaction(Affiliate $affiliate, $data)
    {
        $affiliate->transactions()->save(new AffiliateTransaction($this->withCreateModificationField($data)));
    }

    public function debitWallet(Affiliate $affiliate, $amount, $log_data)
    {
        $affiliate->update(['wallet' => $affiliate->wallet - $amount]);
        $this->walletTransaction($affiliate, $log_data);
    }

    public function makeAmbassador(Affiliate $affiliate)
    {
        $voucher = (new BroadcastPromo($affiliate, null, false))
            ->setVoucherCode($this->getCodeByPhone($affiliate))
            ->saveVoucher()
            ->getVoucher();

        $affiliate->update(["is_ambassador" => 1, 'ambassador_code' => $voucher->code]);
    }

    public function getCodeByPhone(Affiliate $affiliate)
    {
        return preg_replace("/\+88|\+/", '', $affiliate->profile->mobile);
    }
}
