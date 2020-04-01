<?php namespace Sheba\Resource\App\Jobs;


use App\Models\Job;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Dal\Job\JobRepositoryInterface;
use Sheba\Jobs\JobStatuses;

class JobInfo
{
    private $jobRepository;
    private $rearrange;
    private $resource;

    public function __construct(JobRepositoryInterface $job_repository, RearrangeJobList $rearrange)
    {
        $this->jobRepository = $job_repository;
        $this->rearrange = $rearrange;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @param $job_services
     * @return Collection
     */
    public function formatServices($job_services)
    {
        $services = collect();
        foreach ($job_services as $job_service) {
            $services->push([
                'id' => $job_service->service->id,
                'name' => $job_service->service->name,
                'image' => $job_service->service->app_thumb,
                'variables' => json_decode($job_service->variables),
                'unit' => $job_service->service->unit,
                'quantity' => $job_service->quantity
            ]);
        }
        return $services;
    }

    private function getFirstJob()
    {
        $jobs = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->tillNow()->get();
        $jobs = $this->rearrange->rearrange($jobs);
        return $jobs->first();

    }

    public function getJobDetails(Job $job)
    {
        $formatted_job = collect();
        $formatted_job->put('id', $job->id);
        $formatted_job->put('order_code', $job->partnerOrder->order->code());
        $formatted_job->put('customer_id', $job->partnerOrder->order->customer->id);
        $formatted_job->put('customer_name', $job->partnerOrder->order->customer->profile->name);
        $formatted_job->put('pro_pic', $job->partnerOrder->order->customer->profile->pro_pic);
        $formatted_job->put('delivery_name', $job->partnerOrder->order->delivery_name);
        $formatted_job->put('delivery_address', $job->partnerOrder->order->deliveryAddress->address);
        $formatted_job->put('delivery_mobile', $job->partnerOrder->order->delivery_mobile);
        $formatted_job->put('geo_informations', json_decode($job->partnerOrder->order->deliveryAddress->geo_informations));
        $formatted_job->put('start_time', Carbon::parse($job->preferred_time_start)->format('h:i A'));
        $formatted_job->put('schedule_date', $job->schedule_date);
        $formatted_job->put('services', $this->formatServices($job->jobServices));
        $formatted_job->put('status', $job->status);
        $formatted_job->put('can_process', 0);
        $formatted_job->put('can_serve', 0);
        $formatted_job->put('can_collect', 0);
        $formatted_job->put('due', 0);
        if ($this->getFirstJob()->id == $job->id) $this->calculateActionsForThisJob($formatted_job, $job);
        return $formatted_job;
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
}