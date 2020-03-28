<?php namespace Sheba\Resource\App\Jobs;


use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Dal\Job\JobRepositoryInterface;

class JobList
{
    /** @var Resource */
    private $resource;
    /** @var JobRepositoryInterface */
    private $jobRepository;
    private $rearrange;

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
     * @return Collection
     */
    public function getOngoingJobs()
    {
        $jobs = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->tillNow()->get();
        $jobs->load(['partnerOrder' => function ($q) {
            $q->select('id', 'partner_id', 'order_id')->with(['order' => function ($q) {
                $q->select('id', 'sales_channel', 'delivery_address_id', 'delivery_mobile')->with(['deliveryAddress' => function ($q) {
                    $q->select('id', 'name', 'address');
                }]);
            }]);
        }, 'jobServices' => function ($q) {
            $q->select('id', 'job_id', 'service_id')->with(['service' => function ($q) {
                $q->select('id', 'name', 'app_thumb');
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
        foreach ($jobs as $job) {
            $formatted_job = collect();
            $formatted_job->put('id', $job->id);
            $formatted_job->put('order_code', $job->partnerOrder->order->code());
            $formatted_job->put('delivery_address', $job->partnerOrder->order->deliveryAddress->address);
            $formatted_job->put('delivery_mobile', $job->partnerOrder->order->delivery_mobile);
            $formatted_job->put('start_time', Carbon::parse($job->preferred_time_start)->format('h:i A'));
            $formatted_job->put('services', $this->formatServices($job->jobServices));
            $formatted_jobs->push($formatted_job);
        }
        return $formatted_jobs;
    }


    /**
     * @param $job_services
     * @return Collection
     */
    private function formatServices($job_services)
    {
        $services = collect();
        foreach ($job_services as $job_service) {
            $services->push([
                'id' => $job_service->service->id,
                'name' => $job_service->service->name,
                'image' => $job_service->service->app_thumb,
            ]);
        }
        return $services;
    }

}