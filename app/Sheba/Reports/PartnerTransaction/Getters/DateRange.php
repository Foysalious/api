<?php namespace Sheba\Reports\PartnerTransaction\Getters;

use App\Models\PartnerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Reports\PartnerTransaction\Getter;

class DateRange extends Getter
{

    /**
     * @param Request $request
     * @return Collection
     */
    protected function _get(Request $request)
    {
        $partner_transactions = PartnerTransaction::with('partner');
        return $this->notLifetimeQuery($partner_transactions, [$request->start_date, $request->end_date])->get();
    }

    /**
     * @param PartnerTransaction $transaction
     * @return PartnerTransaction
     */
    protected function addProperties(PartnerTransaction $transaction)
    {
        $transaction->partner_id = $transaction->partner->id;
        $transaction->partner_name = $transaction->partner->name;
        return $transaction;
    }
}