<?php namespace App\Sheba\Bondhu;

use App\Models\Affiliate;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AffiliateStatus
{
    private $from;
    private $to;
    private $type;
    private $parent_id;
    private $statuses = array();

    public function setDateRange($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function generateData($affiliate_ids)
    {
        if ($this->type == "affiliates") {
            $this->getData("App\Models\Affiliation", "affiliations", $affiliate_ids, $this->from, $this->to);
            return $this->statuses;
        } else if ($this->type === "partner_affiliates") {
            $this->getData("App\Models\PartnerAffiliation", "partner_affiliations", $affiliate_ids, $this->from, $this->to);
            return $this->statuses;
        }
    }

    public function getIndividualData($affiliate_id)
    {
        $this->parent_id = (Affiliate::find((int)$affiliate_id))->ambassador_id;
        return $this->generateData([$affiliate_id]);
    }

    public function getAgentsData($affiliate_id)
    {
        $this->parent_id = $affiliate_id;
        $affiliateIds = Affiliate::where("ambassador_id", $affiliate_id)->pluck('id');
        return $this->generateData($affiliateIds);
    }

    public function getFormattedDate($request)
    {
        switch ($request->filter_type) {
            case "date_range":
                $this->setDateRange($request->from, $request->to);
                break;
            default:
                $formattedDates = (formatDateRange($request->filter_type));
                $this->setDateRange($formattedDates["from"], $formattedDates["to"]);
                break;
        }
        return $this;
    }

    private function getData($modelName, $tableName, $affiliate_ids, $from, $to)
    {
        $countsQuery = $modelName::join('affiliates', 'affiliates.id', '=', $tableName . '.affiliate_id')
            ->select(
                DB::raw("count(case when date (" . $tableName . ".created_at) >= '" . $from . "' and date(" . $tableName . ".created_at)<= '" . $to . "' then " . $tableName . ".id end) as total_leads"),
                DB::raw("count(case when status='successful' and date (" . $tableName . ".created_at) >= '" . $from . "' and date(" . $tableName . ".created_at)<= '" . $to . "' then " . $tableName . ".id end) as total_successful"),
                DB::raw("count(case when status in('pending', 'follow_up','converted') and date (" . $tableName . ".created_at) >= '" . $from . "' and date(" . $tableName . ".created_at)<= '" . $to . "' then " . $tableName . ".id end) as total_pending"),
                DB::raw("count(case when status='rejected' and date (" . $tableName . ".created_at) >= '" . $from . "' and date(" . $tableName . ".created_at)<= '" . $to . "' then " . $tableName . ".id end) as total_rejected")
            )
            ->whereIn('affiliate_id', $affiliate_ids);

        $countsQuery = $countsQuery->where($tableName . '.created_at', '>=', DB::raw('affiliates.under_ambassador_since'));

        $counts = $countsQuery->get()->toArray();
        $this->statuses = $counts[0];
        $range = getRangeFormat(request(), 'filter_type');
        $earning_amount_query = $modelName::join('affiliate_transactions', $tableName . '.id', '=', 'affiliate_transactions.affiliation_id')
            ->join('affiliates', 'affiliates.id', '=', $tableName . '.affiliate_id')
            ->where('affiliate_transactions.affiliate_id', $this->parent_id)
            ->whereIn($tableName . '.affiliate_id', $affiliate_ids)
            ->where('status', 'successful')
            ->where('affiliate_transactions.is_gifted', 1)
            ->whereRaw('affiliate_transactions.created_at >= affiliates.under_ambassador_since')
            ->whereBetween('affiliate_transactions.created_at', [$range[0], $range[1]]);
        $earning_amount = (double)$earning_amount_query->sum('affiliate_transactions.amount');

        $this->statuses["earning_amount"] = $earning_amount;
        $this->statuses["from_date"] = date("jS F", strtotime($this->from));
        $this->statuses["to_date"] = date("jS F", strtotime($this->to));
    }
}