<?php namespace Sheba\Queries\Order;

use App\Models\JobPartnerChangeLog;
use App\Models\Order;

class PartnerChangeLogsQueries
{
    private $order;
    private $jobs;

    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    private function query()
    {
        $jobs_id = $this->order->jobs->pluck('id')->toArray();

        return JobPartnerChangeLog::join('partners as old_partners', 'old_partners.id', '=', 'job_partner_change_logs.old_partner_id')
            ->join('partners as new_partners', 'new_partners.id', '=', 'job_partner_change_logs.new_partner_id')
            ->whereIn('job_partner_change_logs.job_id', $jobs_id)
            ->select('job_partner_change_logs.*', 'old_partners.name as old_partner_name', 'new_partners.name as new_partner_name');
    }

    public function get()
    {
        return $this->query()->get();
    }
}
