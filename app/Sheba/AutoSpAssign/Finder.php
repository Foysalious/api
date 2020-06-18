<?php namespace Sheba\AutoSpAssign;


use App\Models\PartnerOrderReport;
use Carbon\Carbon;

class Finder
{
    const AVG_RATING = 'avg_rating';
    const RECENT_SERVED_JOB_COUNT = 'recent_served_job_count';
    const SERVED_JOB_COUNT = 'served_job_count';
    const COMPLAIN_COUNT = 'complain_count';
    const ITA_COUNT = 'ita_count';
    const OTA_COUNT = 'ota_count';
    const RESOURCE_APP_USAGE_COUNT = 'resource_app_usage_count';
    const MAX_REVENUE = 'max_rev';
    const PARTNER_ID = 'partner_id';
    const PARTNER_PACKAGE_ID = 'partner_package_id';
    const IMPRESSION_COUNT = 'impression_count';
    private $partnerIds;
    private $category_id;

    /**
     * @param mixed $category_id
     * @return Finder
     */
    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;
        return $this;
    }


    /**
     * @param mixed $partnerIds
     * @return Finder
     */
    public function setPartnerIds($partnerIds)
    {
        $this->partnerIds = $partnerIds;
        return $this;
    }

    /**
     * @return EligiblePartner[]
     */
    public function find()
    {
        $p_reports = $this->getFromDB();
        $total_served_count = $this->getTotalServedCount();
        $partners = [];
        foreach ($p_reports as $p_report) {
            $eligible_partner = new EligiblePartner();
            $eligible_partner->setId($p_report->{self::PARTNER_ID})->setAvgRating($p_report->{self::AVG_RATING})
                ->setComplainCount($p_report->{self::COMPLAIN_COUNT})->setImpressionCount($p_report->{self::IMPRESSION_COUNT})
                ->setItaCount($p_report->{self::ITA_COUNT})->setOtaCount($p_report->{self::OTA_COUNT})->setMaxRevenue($p_report->{self::MAX_REVENUE})
                ->setRecentServedJobCount($p_report->{self::RECENT_SERVED_JOB_COUNT})->setPackageId($p_report->{self::PARTNER_PACKAGE_ID})
                ->setResourceAppUsageCount($p_report->{self::RESOURCE_APP_USAGE_COUNT})
                ->setLifetimeServedJobCount($total_served_count->where(self::PARTNER_ID, $p_report->{self::PARTNER_ID})->first()->{self::SERVED_JOB_COUNT});
            array_push($partners, $eligible_partner);
        }
        return $partners;
    }

    private function getFromDB()
    {
        return PartnerOrderReport::selectRaw("avg(csat) as " . self::AVG_RATING)
            ->selectRaw("count(*) as " . self::RECENT_SERVED_JOB_COUNT)
            ->selectRaw("count(case when user_complaint >0 then user_complaint end) as " . self::COMPLAIN_COUNT)
            ->selectRaw("SUM(TIMESTAMPDIFF(MINUTE, created_date, accept_date) <= 5) as " . self::ITA_COUNT)
//             SUM(case when kind = 1 then 1 else 0 end)
            ->selectRaw("sum(schedule_due_counter=0) as " . self::OTA_COUNT)
            ->selectRaw("count(case when served_from in ('resource-app') then served_from end) as " . self::RESOURCE_APP_USAGE_COUNT)
            ->selectRaw("sum(gmv-sp_cost-discount_partner) as " . self::MAX_REVENUE)
            ->selectRaw("sp_id as " . self::PARTNER_ID)
            ->selectRaw("package_id as " . self::PARTNER_PACKAGE_ID)
            ->selectRaw("partners.current_impression as " . self::IMPRESSION_COUNT)
            ->join('partners', 'partners.id', '=', 'partner_order_report.sp_id')
            ->whereIn('sp_id', $this->partnerIds)
            ->where('service_category_id', $this->category_id)
            ->where([['closed_date', '<>', null], ['partner_order_report.created_date', '>=', Carbon::now()->subMonths(3)->toDateTimeString()]])
            ->groupBy('sp_id')->get();
    }

    private function getTotalServedCount()
    {
        return PartnerOrderReport::selectRaw("count(sp_id) as " . self::SERVED_JOB_COUNT)
            ->selectRaw("sp_id as " . self::PARTNER_ID)
            ->where('closed_date', '<>', null)
            ->whereIn('sp_id', $this->partnerIds)
            ->groupBy('sp_id')->get();
    }
}