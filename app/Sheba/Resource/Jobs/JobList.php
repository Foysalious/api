<?php namespace Sheba\Resource\Jobs;


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
    /** @var Job */
    private $firstJobFromList;
    private $actionCalculator;
    private $statusTagCalculator;

    public function __construct(JobRepositoryInterface $job_repository, RearrangeJobList $rearrange, JobInfo $jobInfo, ActionCalculator $actionCalculator, StatusTagCalculator $statusTagCalculator)
    {
        $this->jobRepository = $job_repository;
        $this->rearrange = $rearrange;
        $this->jobInfo = $jobInfo;
        $this->actionCalculator = $actionCalculator;
        $this->statusTagCalculator = $statusTagCalculator;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    private function setFirstJobFromList(Job $firstJobFromList)
    {
        $this->firstJobFromList = $firstJobFromList;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getOngoingJobs()
    {
        $jobs = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->tillNow()->get();
        $jobs = $this->loadNecessaryRelations($jobs);
        $jobs = $this->rearrange->rearrange($jobs);
        if (count($jobs) > 0) $this->setFirstJobFromList($jobs->first());
        return $this->formatJobs($jobs);
    }

    /**
     * @return Job|null
     */
    public function getNextJob()
    {
        $jobs = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->whereIn('status', [JobStatuses::ACCEPTED, JobStatuses::SCHEDULE_DUE])
            ->where('schedule_date', Carbon::now()->toDateString())
            ->where('preferred_time_start', '=', Carbon::now()->addMinutes(15)->format('H:i'))->get();
        $jobs = $this->formatJobs($jobs);
        return $jobs->first();
    }

    public function getTomorrowsJobs()
    {
        $jobs = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->where('schedule_date', Carbon::tomorrow()->toDateString())->get();
        $jobs = $this->loadNecessaryRelations($jobs);
        $jobs = $this->rearrange->rearrange($jobs);
        return $this->formatJobs($jobs);
    }

    public function getRestJobs()
    {
        $jobs = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->where('schedule_date', '>', Carbon::tomorrow()->toDateString())->get();
        $jobs = $this->loadNecessaryRelations($jobs);
        $jobs = $this->rearrange->rearrange($jobs);
        return $this->formatJobs($jobs);
    }

    private function loadNecessaryRelations($jobs)
    {
        $jobs->load(['partnerOrder' => function ($q) {
            $q->with(['order' => function ($q) {
                $q->select('id', 'sales_channel', 'delivery_address_id', 'delivery_mobile')->with(['deliveryAddress' => function ($q) {
                    $q->select('id', 'name', 'address', 'location_id')->with(['location' => function ($q) {
                        $q->select('id', 'name');
                    }]);
                }]);
            }]);
        }, 'jobServices' => function ($q) {
            $q->select('id', 'variables', 'quantity', 'job_id', 'service_id')->with(['service' => function ($q) {
                $q->select('id', 'name', 'app_thumb', 'unit');
            }]);
        }]);
        return $jobs;
    }

    /**
     * @param Collection $jobs
     * @return Collection
     */
    private function formatJobs(Collection $jobs)
    {
        $formatted_jobs = collect();
        foreach ($jobs as $job) {
            $job->partnerOrder->calculate(1);
            $formatted_job = collect();
            $formatted_job->put('id', $job->id);
            $formatted_job->put('order_code', $job->partnerOrder->order->code());
            $formatted_job->put('category_id', $job->category_id);
            $formatted_job->put('delivery_address', $job->partnerOrder->order->deliveryAddress->address);
            $formatted_job->put('location', $job->partnerOrder->order->deliveryAddress->location->name);
            $formatted_job->put('delivery_mobile', $job->partnerOrder->order->delivery_mobile);
            $formatted_job->put('start_time', Carbon::parse($job->preferred_time_start)->format('h:i A'));
            $formatted_job->put('services', $this->jobInfo->formatServices($job->jobServices));
            $formatted_job->put('order_status', $this->statusTagCalculator->getOrderStatusMessage($job));
            $formatted_job->put('tag', $this->statusTagCalculator->calculateTag($job));
            $formatted_job->put('status', $job->status);
            $formatted_job->put('schedule_date', $job->schedule_date);
            $formatted_job->put('schedule_date_time', Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start)->toDateTimeString());
            $formatted_job->put('can_process', 0);
            $formatted_job->put('can_serve', 0);
            $formatted_job->put('can_collect', 0);
            $formatted_job->put('due', 0);
            if ($this->firstJobFromList && $this->firstJobFromList->id == $job->id) $formatted_job = $this->actionCalculator->calculateActionsForThisJob($formatted_job, $job);
            $formatted_jobs->push($formatted_job);
        }
        return $formatted_jobs;
    }


    public function getNumberOfJobs()
    {
        $jobs_summary = [];
        $jobs_summary['schedule_due_jobs'] = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->tillNow()->where('status', JobStatuses::SCHEDULE_DUE)->count();
        $jobs_summary['todays_jobs'] = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->tillNow()->count();
        $jobs_summary['tomorrows_jobs'] = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->where('schedule_date', Carbon::tomorrow()->toDateString())->count();
        $jobs_summary['rest_jobs'] = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->where('schedule_date', '>', Carbon::tomorrow()->toDateString())->count();
        return $jobs_summary;
    }


}