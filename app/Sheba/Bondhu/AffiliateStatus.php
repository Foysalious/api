<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 10/29/2018
 * Time: 12:51 PM
 */

namespace App\Sheba\Bondhu;
use App\Models\Affiliate;
use App\Models\Affiliation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AffiliateStatus
{
    private $from;
    private $to;
    private $total_lead = 0;
    private $pending_lead = 0;
    private $successfull_lead = 0;
    private $rejected_lead = 0;
    private $earning_amount = 0.00;
    private $type;
    private $parent_id;

    public function __construct()
    {
        $this->total_lead = 0;
        $this->pending_lead = 0;
        $this->successfull_lead = 0;
        $this->rejected_lead = 0;
        $this->earning_amount = 0;
    }

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
        $from = $this->from;
        $to = $this->to;

        if($this->type == "affiliates") {
            $affiliate_statuses = Affiliation::leftJoin('affiliates','affiliates.id','=','affiliations.affiliate_id')
                                    ->selectRaw('affiliations.status, count(affiliations.id) as count')
                                    ->whereIn('affiliate_id',$affiliate_ids)
                                    ->whereDate('under_ambassador_since','>=',$this->from)
                                    ->whereDate('under_ambassador_since','<=',$this->to)
                                    ->whereDate('affiliations.created_at','>=',$from)
                                    ->whereDate('affiliations.created_at','<=',$to)
                                    ->groupBy('affiliations.status')
                                    ->get()->toArray();

            $statuses = array();
            foreach ($affiliate_statuses as $status) {
                if($status["status"] == "pending" || $status["status"] == "follow_up" || $status["status"] == "converted" ) {
                    if(!isset( $statuses["pending"]))
                        $statuses["pending"] = 0;
                    $statuses["pending"] += $status["count"];
                }else{
                    $statuses[$status["status"]] = $status["count"];
                }
            }


            $earning_amount = Affiliation::join('affiliate_transactions','affiliations.id','=','affiliate_transactions.affiliation_id')
            ->where('affiliate_transactions.affiliate_id',$this->parent_id)
            ->whereIn('affiliations.affiliate_id',$affiliate_ids)
            ->sum('amount');
            $statuses["earning_amount"] = $earning_amount;
            return $statuses;
        } else if($this->type === "partner_affiliates"){

        }

//


    }

    public function getIndividualData($affiliate_id)
    {
        $this->parent_id = $affiliate_id;
        $this->generateData([$affiliate_id]);
        return $this->formatData();
    }

    public function getAgentsData($affiliate_id)
    {
        $this->parent_id = $affiliate_id;
        $affiliateIds = Affiliate::where("ambassador_id",$affiliate_id)
                            ->pluck('id');

        return $this->generateData($affiliateIds);

       // return $this->formatData();
    }

    public function formatDateRange($request)
    {
        $currentDate = \Carbon\Carbon::now();
        switch ($request->filter_type) {
            case "today":
                    $this->setDateRange(Carbon::yesterday()->toDateString(),Carbon::today()->toDateString());
                break;
            case "yesterday":
                $this->setDateRange(Carbon::yesterday()->addDay(-1)->toDateString(),Carbon::today()->toDateString());
                break;
            case "week":
                    $this->setDateRange($currentDate->startOfWeek()->addDays(-1)->toDateString(),Carbon::today()->toDateString());
                break;
            case "month":
                    $this->setDateRange($currentDate->startOfMonth()->toDateString(),Carbon::today()->toDateString());
                break;
            case "year":
                $this->setDateRange($currentDate->startOfYear()->toDateString(),Carbon::today()->toDateString());
                break;
            case "all_time":
                $this->setDateRange(Carbon::today()->addDays(-9999)->toDateString(),Carbon::today()->toDateString());
                break;
            case "date_range":
                    $this->setDateRange($request->from, $request->to);
                break;
            default:
                    $this->setDateRange(Carbon::yesterday(),Carbon::today());
                break;
        }
        return $this;
    }

    public function formatData()
    {
        return [
                'total_lead' => $this->total_lead,
                'pending_lead' => $this->pending_lead,
                'successfull_lead' => $this->successfull_lead,
                'rejected_lead' => $this->rejected_lead,
                'earning_amount' => $this->earning_amount
            ];
    }
}