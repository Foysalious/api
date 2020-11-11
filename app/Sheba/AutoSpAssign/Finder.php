<?php namespace Sheba\AutoSpAssign;


use App\Models\Partner;
use App\Models\PartnerOrderReport;
use Carbon\Carbon;
use stdClass;

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
    const RATING_COUNT = 'rating_count';
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
        $total_served_count_of_partners = $this->getTotalServedCount();
        $total_served_count_of_partners = $this->updateAllPartners($total_served_count_of_partners);
        $partners = [];
        foreach ($total_served_count_of_partners as $total_served_count_of_partner) {
            $eligible_partner = new EligiblePartner();
            $eligible_partner->setId($total_served_count_of_partner->{self::PARTNER_ID});
            $partner = $p_reports->where(self::PARTNER_ID, $total_served_count_of_partner->{self::PARTNER_ID})->first();
            if (!$partner) $eligible_partner->setLifetimeServedJobCount($total_served_count_of_partner->{self::SERVED_JOB_COUNT})
                ->setImpressionCount($total_served_count_of_partner->{self::IMPRESSION_COUNT})->setPackageId($total_served_count_of_partner->{self::PARTNER_PACKAGE_ID});
            else
                $eligible_partner->setAvgRating($partner->{self::AVG_RATING})
                    ->setComplainCount($partner->{self::COMPLAIN_COUNT})->setImpressionCount($partner->{self::IMPRESSION_COUNT})
                    ->setItaCount($partner->{self::ITA_COUNT})->setOtaCount($partner->{self::OTA_COUNT})->setMaxRevenue($partner->{self::MAX_REVENUE})
                    ->setRecentServedJobCount($partner->{self::RECENT_SERVED_JOB_COUNT})->setPackageId($partner->{self::PARTNER_PACKAGE_ID})
                    ->setResourceAppUsageCount($partner->{self::RESOURCE_APP_USAGE_COUNT})
                    ->setLifetimeServedJobCount($total_served_count_of_partner->{self::SERVED_JOB_COUNT});

            array_push($partners, $eligible_partner);
        }
        return $partners;
    }

    private function getFromDB()
    {
        return PartnerOrderReport::selectRaw("avg(csat) as " . self::AVG_RATING)
            ->selectRaw("count(*) as " . self::RECENT_SERVED_JOB_COUNT)
            ->selectRaw("count(case when csat is not null then csat end) as " . self::RATING_COUNT)
            ->selectRaw("count(case when user_complaint >0 then user_complaint end) as " . self::COMPLAIN_COUNT)
            ->selectRaw("SUM(TIMESTAMPDIFF(MINUTE, (case when request_created_date is not null then request_created_date else created_date end), accept_date) <= 5) as " . self::ITA_COUNT)
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
        return
            PartnerOrderReport::selectRaw("count(sp_id) as " . self::SERVED_JOB_COUNT)
                ->selectRaw("sp_id as " . self::PARTNER_ID)
                ->selectRaw("package_id as " . self::PARTNER_PACKAGE_ID)
                ->selectRaw("partners.current_impression as " . self::IMPRESSION_COUNT)
                ->rightJoin('partners', 'partners.id', '=', 'partner_order_report.sp_id')
                ->where('closed_date', '<>', null)
                ->where('service_category_id', $this->category_id)
                ->whereIn('sp_id', $this->partnerIds)
                ->groupBy('sp_id')->get();
    }

    private function updateAllPartners($partners_with_total_served_jobs)
    {
        $remaining_partners = array_diff($this->partnerIds, $partners_with_total_served_jobs->pluck('partner_id')->toArray());
        $remaining_partners = Partner::whereIn('id', $remaining_partners)->get();
        foreach ($remaining_partners as $remaining_partner) {
            $partner = new stdClass();
            $partner->partner_id = $remaining_partner->id;
            $partner->{self::SERVED_JOB_COUNT} = 0;
            $partner->{self::IMPRESSION_COUNT} = $remaining_partner->current_impression;
            $partner->{self::PARTNER_PACKAGE_ID} = $remaining_partner->package_id;
            $partners_with_total_served_jobs->push($partner);
        }
        return $partners_with_total_served_jobs;
    }
}