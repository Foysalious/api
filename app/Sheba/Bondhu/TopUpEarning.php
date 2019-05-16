<?php namespace App\Sheba\Bondhu;


use App\Models\Affiliate;
use App\Models\TopUpOrder;

class TopUpEarning
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
                $formattedDates = (getRangeFormat(request(), 'filter_type'));
                $this->setDateRange($formattedDates[0], $formattedDates[1]);
                break;
        }
        return $this;
    }

    private function getData( $affiliate_ids)
    {
        $vendor_amounts = TopUpOrder::join('topup_vendors','topup_orders.vendor_id','=','topup_vendors.id')
            ->selectRaw('name, Sum(topup_orders.Amount) as amount')
            ->where('agent_type',Affiliate::class)->whereIn('agent_id',$affiliate_ids)->groupBy('vendor_id')
            ->whereBetween('topup_orders.created_at', [$this->from, $this->to])->get();

        $earning_amount = (double)  TopUpOrder::join('topup_vendors','topup_orders.vendor_id','=','topup_vendors.id')
            ->where('agent_type',Affiliate::class)->whereIn('agent_id',$affiliate_ids)
            ->whereBetween('topup_orders.created_at', [$this->from, $this->to])->sum('agent_commission');

        $total_amount = (double)  TopUpOrder::join('topup_vendors','topup_orders.vendor_id','=','topup_vendors.id')
            ->where('agent_type',Affiliate::class)->whereIn('agent_id',$affiliate_ids)
            ->whereBetween('topup_orders.created_at', [$this->from, $this->to])->sum('topup_orders.amount');

        $this->statuses['vendor_amounts'] = $vendor_amounts;
        $this->statuses['earning_amount'] = $earning_amount;
        $this->statuses['total_amount'] = $total_amount;
        $this->statuses["from_date"] = date("jS F", strtotime($this->from));
        $this->statuses["to_date"] = date("jS F", strtotime($this->to));
    }

}