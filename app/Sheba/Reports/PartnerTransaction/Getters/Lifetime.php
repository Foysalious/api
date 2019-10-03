<?php namespace Sheba\Reports\PartnerTransaction\Getters;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Reports\PartnerTransaction\Getter;

class Lifetime extends Getter
{
    private $balance = 0;

    /**
     * @param Request $request
     * @return Collection
     */
    protected function _get(Request $request)
    {
        $partner = Partner::find($request->partner_id);
        return $partner->transactions;
    }

    /**
     * @param PartnerTransaction $transaction
     * @return PartnerTransaction
     */
    protected function addProperties(PartnerTransaction $transaction)
    {
        $modifier = $transaction->type == "Credit" ? 1 : -1;
        $this->balance = $this->balance + ($transaction->amount * $modifier);
        $transaction->balance = $this->balance;
        return $transaction;
    }
}