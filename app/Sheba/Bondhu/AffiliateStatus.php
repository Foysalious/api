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

    public function generateData($affiliate_ids) {
        if($this->type == "affiliates") {
            $this->getData("App\Models\Affiliation","affiliations",$affiliate_ids,$this->from,$this->to);
            return  $this->statuses;
        } else if($this->type === "partner_affiliates"){
            $this->getData("App\Models\PartnerAffiliation","partner_affiliations",$affiliate_ids,$this->from,$this->to);
            return $this->statuses;
        }
    }

    public function getIndividualData($affiliate_id)
    {
        $this->parent_id = $affiliate_id;
        $this->generateData([$affiliate_id]);
    }

    public function getAgentsData($affiliate_id)
    {
        $this->parent_id = $affiliate_id;
        $affiliateIds = Affiliate::where("ambassador_id",$affiliate_id)->pluck('id');

        return $this->generateData($affiliateIds);
    }

    public function formatDateRange($request)
    {
        $currentDate = Carbon::now();

        switch ($request->filter_type) {
            case "today":
                    $this->setDateRange(Carbon::yesterday()->toDateString(), Carbon::today()->toDateString());
                break;
            case "yesterday":
                $this->setDateRange(Carbon::yesterday()->addDay(-1)->toDateString(), Carbon::today()->toDateString());
                break;
            case "week":
                    $this->setDateRange($currentDate->startOfWeek()->addDays(-1)->toDateString(), Carbon::today()->toDateString());
                break;
            case "month":
                    $this->setDateRange($currentDate->startOfMonth()->toDateString(), Carbon::today()->toDateString());
                break;
            case "year":
                $this->setDateRange($currentDate->startOfYear()->toDateString(), Carbon::today()->toDateString());
                break;
            case "all_time":
                $this->setDateRange('2017-01-01', Carbon::today()->toDateString());
                break;
            case "date_range":
                    $this->setDateRange($request->from, $request->to);
                break;
            default:
                    $this->setDateRange(Carbon::yesterday(), Carbon::today());
                break;
        }
        return $this;
    }

    private function getData($modelName, $tableName, $affiliate_ids, $from, $to) {

        $counts = $modelName::join('affiliates','affiliates.id','=',$tableName.'.affiliate_id')
            ->select(
                DB::raw("count(case when date (affiliations.created_at) >= '".$from."' and date(affiliations.created_at)<= '".$to."' then affiliations.id end) as total_leads"),
                DB::raw("count(case when status='successful' and date (affiliations.created_at) >= '".$from."' and date(affiliations.created_at)<= '".$to."' then affiliations.id end) as total_successful"),
                DB::raw("count(case when status='pending' or status='follow_up' or status='converted' and date (affiliations.created_at) >= '".$from."' and date(affiliations.created_at)<= '".$to."' then affiliations.id end) as total_pending"),
                DB::raw("count(case when status='rejected' and date (affiliations.created_at) >= '".$from."' and date(affiliations.created_at)<= '".$to."' then affiliations.id end) as total_rejected")
            )
            ->whereIn('affiliate_id',$affiliate_ids)
            ->where($tableName.'.created_at','>=',DB::raw('affiliates.under_ambassador_since'))
            ->get()->toArray();
        
        $this->statuses = $counts[0];

        $earning_amount = $modelName::join('affiliate_transactions',$tableName.'.id','=','affiliate_transactions.affiliation_id')
            ->join('affiliates','affiliates.id','=',$tableName.'.affiliate_id')
            ->where('affiliate_transactions.created_at','>=',DB::raw('affiliates.under_ambassador_since'))
            ->where('affiliate_transactions.affiliate_id',$this->parent_id)
            ->whereIn($tableName.'.affiliate_id',$affiliate_ids)
            ->sum('amount');

        $this->statuses["earning_amount"] = $earning_amount;
    }
}