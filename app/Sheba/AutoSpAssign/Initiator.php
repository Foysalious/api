<?php namespace Sheba\AutoSpAssign;


use App\Models\Customer;
use App\Models\Order;
use App\Models\PartnerOrderReport;
use Carbon\Carbon;

class Initiator
{
    /** @var Customer */
    private $customer;
    /** @var Order */
    private $order;
    /** @var array */
    private $partnerIds;

    /**
     * @param Customer $customer
     * @return Initiator
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param array $partnerIds
     * @return Initiator
     */
    public function setPartnerIds($partnerIds)
    {
        $this->partnerIds = $partnerIds;
        return $this;
    }

    /**
     * @param Order $order
     * @return Initiator
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function initiate()
    {
        $partner_order_reports = $this->getEligiblePartners();
    }

    private function getEligiblePartners()
    {
        $p_reports = $this->get();
        dd($p_reports);
    }

    private function get()
    {
        return PartnerOrderReport::selectRaw("avg(csat) as " . QueryAliases::AVG_RATING)
            ->selectRaw("count(*) as " . QueryAliases::JOB_COUNT)
            ->selectRaw("count(case when user_complaint >0 then user_complaint end) as " . QueryAliases::COMPLAIN_COUNT)
            ->selectRaw("SUM(TIMESTAMPDIFF(MINUTE, created_date, accept_date) <= 5) as " . QueryAliases::ITA_COUNT)
//             SUM(case when kind = 1 then 1 else 0 end)
            ->selectRaw("sum(schedule_due_counter=0) as " . QueryAliases::OTA_COUNT)
            ->selectRaw("count(case when status_changes NOT LIKE '%due%' then status_changes end) as " . QueryAliases::SCHEDULE_JOB_COUNT)
            ->selectRaw("count(case when served_from in ('resource-app') then served_from end) as " . QueryAliases::SPO_USAGE_COUNT)
            ->selectRaw("sum(gmv-sp_cost-discount_partner) as max_rev" . QueryAliases::MAX_REVENUE)
            ->whereIn('sp_id', $this->partnerIds)
            ->where('service_category_id', 14)
            ->where([['closed_date', '<>', null], ['partner_order_report.created_date', '>=', Carbon::now()->subMonths(3)->toDateTimeString()]])
            ->selectRaw("sp_id as " . QueryAliases::PARTNER_ID)
            ->selectRaw("partners.current_impression as " . QueryAliases::IMPRESSION_COUNT)
            ->join('partners', 'partners.id', '=', 'partner_order_report.sp_id')
            ->groupBy('sp_id')->get();
    }
}