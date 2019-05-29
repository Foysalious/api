<?php namespace Sheba\Reports\PartnerPayments;

use App\Models\Job;
use App\Models\JobDeclineLog;
use App\Models\Partner;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class SinglePartnerPayment extends PartnerPayments
{
    /** @var array */
    private $data;

    /** @var Partner */
    private $partner;
    /** @var Collection */
    private $partnerOrders;

    private $satisfactionLevels;

    public function __construct()
    {
        $this->initialize();
        $this->satisfactionLevels = array_values(constants('JOB_SATISFACTION_LEVELS'));
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function calculate()
    {
        /** @var Builder $partner_orders */
        $partner_orders = $this->partner->orders()->with('jobs');
        if ($this->request->payable_session != 'lifetime') {
            $this->partnerOrders = $this->notLifetimeQuery($partner_orders, $this->session, 'closed_at')->get();
        } else {
            $this->partnerOrders = $partner_orders->whereNotNull('closed_at')->get();
            $this->session['start_date'] = $this->partnerOrders->count() ? $this->partnerOrders->first()->closed_at : '2017-01-01 00:00:00';
            $this->session['end_date'] = Carbon::now()->toDateString();
        }

        foreach ($this->partnerOrders as $partner_order) {
            /** @var PartnerOrder $partner_order */
            $partner_order = $partner_order->calculate();
            $this_order_jobs = $partner_order->jobs;
            $this->data['total_order']++;
            $this->data['total_job_assigned'] += count($this_order_jobs);
            $this->data['total_order_price'] += $partner_order->jobPrices;
            $this->data['total_discount'] += $partner_order->totalDiscount + $partner_order->roundingCutOff;
            $this->data['total_order_amount'] += $partner_order->grossAmount;
            $this->data['collected_by_sheba'] += $partner_order->sheba_collection;
            $this->data['collected_by_sp'] += $partner_order->partner_collection;
            $this->data['total_collection'] += $partner_order->paid;
            $this->data['total_due'] += $partner_order->due;
            $this->data['total_service_cost'] += $partner_order->totalServiceCost;
            $this->data['total_material_cost'] += $partner_order->totalMaterialCost;
            $this->data['total_order_cost'] += $partner_order->totalCost;
            $this->data['profit'] += $partner_order->profit;
            $this->data['profit_before_discount'] += $partner_order->profitBeforeDiscount;

            foreach ($partner_order->jobs as $job) {
                /** @var Job $job */
                if ($job->isCancelled()) $this->data['total_jobs_cancelled']++;
                $this->data['satisfaction_level'] += array_search($job->satisfaction_level, $this->satisfactionLevels);
                $this->data['job_complain'] += $job->complains->count();
            }
        }

        $this->data['total_jobs_declined'] = $this->getThisSessionDeclinedJobsCount();
        if ($this->data['total_order_amount']) {
            $this->data['profit_margin'] = (($this->data['total_order_amount'] - $this->data['total_order_cost']) * 100) / $this->data['total_order_amount'];
        }

        if ($this->data['total_order_price']) {
            $this->data['profit_margin_before_discount'] = (($this->data['total_order_price'] - $this->data['total_order_cost']) * 100) / $this->data['total_order_price'];
        }

        $this->data['session_times'] = $this->session;
        $this->data['total_job'] = $this->data['total_job_assigned'] - $this->data['total_jobs_cancelled'];
        $this->data['satisfaction_level'] = ($this->data['total_job']) ? $this->data['satisfaction_level'] / $this->data['total_job'] : 0;
        $this->data['sp_payable'] = ($this->data['collected_by_sp'] < $this->data['total_order_cost']) ? ($this->data['total_order_cost'] - $this->data['collected_by_sp']) : 0;
        $this->data['sheba_receivable'] = ($this->data['collected_by_sp'] > $this->data['total_order_cost']) ? ($this->data['collected_by_sp'] - $this->data['total_order_cost']) : 0;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getPartnerOrders()
    {
        return $this->partnerOrders;
    }

    private function initialize()
    {
        $this->data = [
            'total_order' => 0,
            'total_job' => 0,
            'total_order_price' => 0,
            'total_discount' => 0,
            'total_order_amount' => 0,
            'collected_by_sheba' => 0,
            'collected_by_sp' => 0,
            'total_collection' => 0,
            'total_due' => 0,
            'total_service_cost' => 0,
            'total_material_cost' => 0,
            'total_order_cost' => 0,
            'profit' => 0,
            'profit_margin' => 0,
            'profit_before_discount' => 0,
            'profit_margin_before_discount' => 0,
            'total_job_assigned' => 0,
            'total_jobs_declined' => 0,
            'total_jobs_cancelled' => 0,
            'satisfaction_level' => 0,
            'job_complain' => 0,
        ];
    }

    /**
     * Get count of all declined jobs in given session of a partner.
     *
     * @return int
     */
    private function getThisSessionDeclinedJobsCount()
    {
        $job_decline_log = JobDeclineLog::where('partner_id', $this->partner->id);
        $job_decline_log = $this->notLifetimeQuery($job_decline_log, $this->session);
        $total_jobs_declined = $job_decline_log->count();
        return $total_jobs_declined;
    }
}