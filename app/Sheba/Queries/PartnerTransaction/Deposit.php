<?php namespace Sheba\Queries\PartnerTransaction;

use App\Models\PartnerTransaction;
use Illuminate\Support\Facades\DB;

class Deposit
{
    private $partner;

    public function __construct($partner = null)
    {
        $this->partner = $partner;
    }

    public function all()
    {
        $all_partner_deposit_query = PartnerTransaction::whereRaw('log like "%paid to SHEBA."');
        if ($this->partner) {
            return $all_partner_deposit_query->where('partner_id', $this->partner->id)->get();
        }
        return $all_partner_deposit_query->get();
    }

    public function last()
    {
        if ($this->partner) {
            return $this->depositQuery()->where('partner_transactions.partner_id', $this->partner->id)->first();
        }
        return $this->depositQuery()->get();
    }

    public function lastDepositDateQuery()
    {
        return PartnerTransaction::select('partner_transactions.partner_id', DB::raw('MAX(created_at) AS last_deposit_date'))
            ->whereRaw('log like "%paid to SHEBA."')
            ->groupBy('partner_id');
    }

    public function depositQuery()
    {
        return PartnerTransaction::select('partner_transactions.*')
            ->join(DB::raw('(' . $this->lastDepositDateQuery()->toSql() . ') partner_transaction_grouped'), function ($join) {
                $join->on('partner_transactions.partner_id', '=', 'partner_transaction_grouped.partner_id')
                     ->on('partner_transactions.created_at', '=', 'partner_transaction_grouped.last_deposit_date');
            });
    }
}

/**
 * RAW QUERY
 *
SELECT pt1.*
FROM partner_transactions pt1
INNER JOIN
    (SELECT partner_id, MAX(created_at) AS MaxDate
    FROM partner_transactions
    where log LIKE "%paid to SHEBA."
    GROUP BY partner_id) partner_transaction_grouped
ON pt1.partner_id = partner_transaction_grouped.partner_id AND pt1.created_at = partner_transaction_grouped.MaxDate;
*/