<?php namespace Sheba\Resource\Jobs;


use App\Models\Job;
use App\Models\Resource;
use Illuminate\Support\Collection;
use Sheba\Dal\Job\JobRepositoryInterface;

class JobInfo
{
    private $jobRepository;
    private $rearrange;
    private $resource;
    private $actionCalculator;
    private $statusTagCalculator;

    public function __construct(JobRepositoryInterface $job_repository, RearrangeJobList $rearrange, ActionCalculator $actionCalculator, StatusTagCalculator $statusTagCalculator)
    {
        $this->jobRepository = $job_repository;
        $this->rearrange = $rearrange;
        $this->actionCalculator = $actionCalculator;
        $this->statusTagCalculator = $statusTagCalculator;
    }

    /**
     * @param Resource $resource
     * @return $this
     */public function setResource(Resource $resource)
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
                'id' => $job_service->id,
                'service_id' => $job_service->service_id,
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
        if (count($jobs) > 0) return $jobs->first();
    }

    /**
     * @param Job $job
     * @return Collection
     */
    public function getJobDetails(Job $job)
    {
        $formatted_job = collect();
        $formatted_job->put('id', $job->id);
        $formatted_job->put('category_name', $job->category->name);
        $formatted_job->put('order_code', $job->partnerOrder->order->code());
        $formatted_job->put('customer_id', $job->partnerOrder->order->customer->id);
        $formatted_job->put('customer_name', $job->partnerOrder->order->customer->profile->name);
        $formatted_job->put('pro_pic', $job->partnerOrder->order->customer->profile->pro_pic);
        $formatted_job->put('delivery_name', $job->partnerOrder->order->delivery_name);
        $formatted_job->put('location', $job->partnerOrder->order->deliveryAddress && $job->partnerOrder->order->deliveryAddress->location ? $job->partnerOrder->order->deliveryAddress->location->name : null);
        $formatted_job->put('delivery_address', $job->partnerOrder->order->deliveryAddress ? $job->partnerOrder->order->deliveryAddress->address : $job->partnerOrder->order->delivery_address);
        $formatted_job->put('delivery_mobile', $job->partnerOrder->order->deliveryAddress ? $job->partnerOrder->order->deliveryAddress->mobile :  $job->partnerOrder->order->delivery_mobile);
        $formatted_job->put('geo_informations', $job->partnerOrder->order->deliveryAddress ? json_decode($job->partnerOrder->order->deliveryAddress->geo_informations) : null);
        $formatted_job->put('start_time', humanReadableShebaTime($job->preferred_time_start, true));
        $formatted_job->put('schedule_date', $job->schedule_date);
        $formatted_job->put('closed_at_date', $job->partnerOrder->closed_at != null ? $job->partnerOrder->closed_at->format('Y-m-d') : null);
        $formatted_job->put('services', $this->formatServices($job->jobServices));
        $formatted_job->put('job_additional_info', $job->job_additional_info);
        $formatted_job->put('is_rent_a_car', $job->isRentCar());
        $formatted_job->put('pick_up_location', $job->carRentalJobDetail && $job->carRentalJobDetail->pickUpLocation ? $job->carRentalJobDetail->pickUpLocation->name : null);
        $formatted_job->put('pick_up_address', $job->carRentalJobDetail ? $job->carRentalJobDetail->pick_up_address : null);
        $formatted_job->put('pick_up_address_geo', $job->carRentalJobDetail ? json_decode($job->carRentalJobDetail->pick_up_address_geo) : null);
        $formatted_job->put('destination_location', $job->carRentalJobDetail && $job->carRentalJobDetail->destinationLocation ? $job->carRentalJobDetail->destinationLocation->name : null);
        $formatted_job->put('destination_address', $job->carRentalJobDetail ? $job->carRentalJobDetail->destination_address : null);
        $formatted_job->put('destination_address_geo', $job->carRentalJobDetail ? json_decode($job->carRentalJobDetail->destination_address_geo) : null);
        $formatted_job->put('rating', $job->review ? $job->review->rating : null);
        $formatted_job->put('tag', $this->statusTagCalculator->calculateTag($job));
        $formatted_job->put('status', $job->status);
        $formatted_job->put('can_process', 0);
        $formatted_job->put('can_serve', 0);
        $formatted_job->put('can_collect', 0);
        $formatted_job->put('due', 0);
        if ($this->getFirstJob() && $this->getFirstJob()->id == $job->id) $this->actionCalculator->calculateActionsForThisJob($formatted_job, $job);
        return $formatted_job;
    }
}