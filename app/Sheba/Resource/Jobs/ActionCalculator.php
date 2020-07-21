<?php namespace Sheba\Resource\Jobs;


use App\Models\Job;
use Sheba\Jobs\JobStatuses;

class ActionCalculator
{
    /**
     * First process, collect then serve
     * @param $formatted_job
     * @param Job $job
     * @return mixed
     */
    public function calculateActionsForThisJob($formatted_job, Job $job)
    {
        $partner_order = $job->partnerOrder;
        $partner_order->calculate();
        if (($job->status == JobStatuses::PROCESS || $job->status == JobStatuses::SERVE_DUE) && $partner_order->due > 0) {
            $formatted_job->put('can_collect', 1);
        } elseif (($job->status == JobStatuses::PROCESS || $job->status == JobStatuses::SERVE_DUE) && $partner_order->due == 0) {
            $formatted_job->put('can_serve', 1);
        } elseif ($job->status == JobStatuses::SERVED && $partner_order->due > 0) {
            $formatted_job->put('can_collect', 1);
        } elseif ($this->isStatusBeforeProcess($job->status)) {
            $formatted_job->put('can_process', 1);
        }
        if (!$partner_order->isClosedAndPaidAt()) $formatted_job->put('due', (double)$partner_order->due);
        return $formatted_job;
    }

    /**
     * @param $status
     * @return bool
     */
    public function isStatusBeforeProcess($status)
    {
        return constants('JOB_STATUS_SEQUENCE')[$status] < constants('JOB_STATUS_SEQUENCE')[JobStatuses::PROCESS];
    }
}