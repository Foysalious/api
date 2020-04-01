<?php namespace Sheba\Resource\App\Jobs;


use App\Models\Job;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\BanglaConverter;
use Sheba\Dal\Job\JobRepositoryInterface;
use Sheba\Jobs\JobStatuses;

class JobList
{
    /** @var Resource */
    private $resource;
    /** @var JobRepositoryInterface */
    private $jobRepository;
    private $rearrange;
    private $jobInfo;

    public function __construct(JobRepositoryInterface $job_repository, RearrangeJobList $rearrange, JobInfo $jobInfo)
    {
        $this->jobRepository = $job_repository;
        $this->rearrange = $rearrange;
        $this->jobInfo = $jobInfo;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getOngoingJobs()
    {
        $jobs = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->tillNow()->get();
        $jobs->load(['partnerOrder' => function ($q) {
            $q->with(['order' => function ($q) {
                $q->select('id', 'sales_channel', 'delivery_address_id', 'delivery_mobile')->with(['deliveryAddress' => function ($q) {
                    $q->select('id', 'name', 'address');
                }]);
            }]);
        }, 'jobServices' => function ($q) {
            $q->select('id', 'variables', 'quantity', 'job_id', 'service_id')->with(['service' => function ($q) {
                $q->select('id', 'name', 'app_thumb', 'unit');
            }]);
        }]);
        $jobs = $this->rearrange->rearrange($jobs);
        return $this->formatJobs($jobs);
    }

    /**
     * @param Collection $jobs
     * @return Collection
     */
    private function formatJobs(Collection $jobs)
    {
        $formatted_jobs = collect();
        $first_job = $jobs->first();
        foreach ($jobs as $job) {
            $formatted_job = collect();
            $formatted_job->put('id', $job->id);
            $formatted_job->put('order_code', $job->partnerOrder->order->code());
            $formatted_job->put('delivery_address', $job->partnerOrder->order->deliveryAddress->address);
            $formatted_job->put('delivery_mobile', $job->partnerOrder->order->delivery_mobile);
            $formatted_job->put('start_time', Carbon::parse($job->preferred_time_start)->format('h:i A'));
            $formatted_job->put('services', $this->jobInfo->formatServices($job->jobServices));
            $formatted_job->put('order_status_message', $this->getOrderStatusMessage($job));
            $formatted_job->put('tag', $this->calculateTag($job));
            $formatted_job->put('status', $job->status);
            $formatted_job->put('can_process', 0);
            $formatted_job->put('can_serve', 0);
            $formatted_job->put('can_collect', 0);
            $formatted_job->put('due', 0);
            if ($first_job->id == $job->id) $formatted_job = $this->calculateActionsForThisJob($formatted_job, $job);
            $formatted_jobs->push($formatted_job);
        }
        return $formatted_jobs;
    }

    private function getOrderStatusMessage(Job $job)
    {
        if ($this->isStatusAfterOrEqualToProcess($job->status)) {
            return "যে অর্ডার টি এখন চলছে";
        } else {
            $job_start_time = $this->getJobStartTime($job);
            $different_in_minutes = Carbon::now()->diffInRealMinutes($job_start_time);
            $hour = floor($different_in_minutes / 60);
            $minute = $different_in_minutes > 60 ? $different_in_minutes % 60 : $different_in_minutes;
            $hr_message = $hour > 0 ? ($hour . ' ঘণ্টা') : '';
            $min_message = $minute > 0 ? ($minute . ' মিনিট') : '';
            if (!empty($min_message) && !empty($hr_message)) $hr_message .= ' ';
            if (Carbon::now()->lt($job_start_time)) {
                $message = "পরের অর্ডার";
            } else {
                $message = "লেট";
            }
            return BanglaConverter::en2bn($hr_message . $min_message) . ' ' . $message;
        }
    }

    private function calculateTag(Job $job)
    {
        $now = Carbon::now();
        $job_start_time = $this->getJobStartTime($job);
        if ($now->gt($job_start_time) && $this->isStatusBeforeProcess($job->status)) return ['type' => 'late', 'value' => 'Late'];
        if ($this->isStatusAfterOrEqualToProcess($job->status)) return ['type' => 'process', 'value' => 'Process'];
        if ($job_start_time->gt($now) && $job_start_time->diffInHours($now) <= 24) return ['type' => 'time', 'value' => Carbon::parse($job->preferred_time_start)->format('H:i A')];
        return ['type' => 'date', 'value' => Carbon::parse($job->schedule_date)->format('j F')];
    }

    /**
     * @param $status
     * @return bool
     */
    private function isStatusBeforeProcess($status)
    {
        return constants('JOB_STATUS_SEQUENCE')[$status] < constants('JOB_STATUS_SEQUENCE')[JobStatuses::PROCESS];
    }

    /**
     * @param $status
     * @return bool
     */
    private function isStatusAfterOrEqualToProcess($status)
    {
        return constants('JOB_STATUS_SEQUENCE')[$status] >= constants('JOB_STATUS_SEQUENCE')[JobStatuses::PROCESS];
    }

    /**
     * First process, collect then serve
     * @param $formatted_job
     * @param Job $job
     * @return mixed
     */
    private function calculateActionsForThisJob($formatted_job, Job $job)
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
     * @param Job $job
     * @return Carbon
     */
    private function getJobStartTime(Job $job)
    {
        return Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start);
    }


}