<?php namespace Sheba\Resource\Jobs;


use App\Models\Job;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Dal\Job\JobRepositoryInterface;
use Sheba\Jobs\JobStatuses;
use DB;

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
    /** @var int $limit */
    private $limit;
    /** @var int $offset */
    private $offset;
    /** @var int $year */
    private $year;
    /** @var int $month */
    private $month;
    private $orderId;
    private $query;

    public function __construct(JobRepositoryInterface $job_repository, RearrangeJobList $rearrange, JobInfo $jobInfo, ActionCalculator $actionCalculator, StatusTagCalculator $statusTagCalculator)
    {
        $this->jobRepository = $job_repository;
        $this->rearrange = $rearrange;
        $this->jobInfo = $jobInfo;
        $this->actionCalculator = $actionCalculator;
        $this->statusTagCalculator = $statusTagCalculator;
        $this->limit = 10;
        $this->offset = 0;
        $this->year = Carbon::now()->format('Y');
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param $year
     * @return $this
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @param $month
     * @return $this
     */
    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }


    public function setQuery($query)
    {
        $this->query = $query;
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

    public function getHistoryJobs()
    {
        $query = $this->jobRepository->getHistoryJobsForResource($this->resource->id);
        $query = $this->historyJobsFilterQuery($query);
        $jobs = $query->orderBy('closed_at', 'DESC')->skip($this->offset)->take($this->limit)->get();
        $jobs = $this->loadNecessaryRelations($jobs);
        $jobs = $this->groupJobsByYearAndMonth($jobs);
        return $this->formatHistoryJobs($jobs);
    }

    public function getJobsFilteredByOrderId()
    {
        $jobs = $this->jobRepository->getJobsForResourceFilteredByOrderId($this->resource->id, $this->orderId)->get();
        $jobs = $this->loadNecessaryRelations($jobs);
        return $this->formatJobs($jobs);
    }

    public function getJobsFilteredByServiceOrCustomerName()
    {
        $jobs = $this->jobRepository->getJobsForResourceFilteredByServiceOrCustomerName($this->resource->id, $this->query)->skip($this->offset)->take($this->limit)->get();
        $jobs = $this->loadNecessaryRelations($jobs);
        return $this->formatJobs($jobs);
    }

    private function loadNecessaryRelations($jobs)
    {
        $jobs->load(['partnerOrder' => function ($q) {
            $q->with(['order' => function ($q) {
                $q->select('id', 'sales_channel', 'delivery_address_id', 'delivery_mobile', 'business_id')->with(['deliveryAddress' => function ($q) {
                    $q->select('id', 'name', 'address', 'mobile', 'location_id')->with(['location' => function ($q) {
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
            $formatted_job->put('total_price', (double)$job->partnerOrder->totalPrice);
            $formatted_job->put('category_id', $job->category_id);
            $formatted_job->put('category_name', $job->category->name);
            $formatted_job->put('delivery_address', $job->partnerOrder->order->deliveryAddress ? $job->partnerOrder->order->deliveryAddress->address : $job->partnerOrder->order->delivery_address);
            $formatted_job->put('location', $job->partnerOrder->order->deliveryAddress && $job->partnerOrder->order->deliveryAddress->location ? $job->partnerOrder->order->deliveryAddress->location->name : null);
            $formatted_job->put('delivery_mobile', $job->partnerOrder->order->deliveryAddress ? $job->partnerOrder->order->deliveryAddress->mobile : $job->partnerOrder->order->delivery_mobile);
            $formatted_job->put('start_time', Carbon::parse($job->preferred_time_start)->format('h:i A'));
            $formatted_job->put('services', $this->jobInfo->formatServices($job->jobServices));
            $formatted_job->put('is_rent_a_car', $job->isRentCar());
            $formatted_job->put('pick_up_location', $job->carRentalJobDetail && $job->carRentalJobDetail->pickUpLocation ? $job->carRentalJobDetail->pickUpLocation->name : null);
            $formatted_job->put('pick_up_address', $job->carRentalJobDetail ? $job->carRentalJobDetail->pick_up_address : null);
            $formatted_job->put('pick_up_address_geo', $job->carRentalJobDetail ? json_decode($job->carRentalJobDetail->pick_up_address_geo) : null);
            $formatted_job->put('destination_location', $job->carRentalJobDetail && $job->carRentalJobDetail->destinationLocation ? $job->carRentalJobDetail->destinationLocation->name : null);
            $formatted_job->put('destination_address', $job->carRentalJobDetail ? $job->carRentalJobDetail->destination_address : null);
            $formatted_job->put('destination_address_geo', $job->carRentalJobDetail ? json_decode($job->carRentalJobDetail->destination_address_geo) : null);
            $formatted_job->put('rating', $job->review ? $job->review->rating : null);
            $formatted_job->put('order_status', $this->statusTagCalculator->getOrderStatusMessage($job));
            $formatted_job->put('tag', $this->statusTagCalculator->calculateTag($job));
            $formatted_job->put('status', $job->status);
            $formatted_job->put('schedule_date', $job->schedule_date);
            $formatted_job->put('schedule_date_time', Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start)->toDateTimeString());
            $formatted_job->put('closed_at_date', $job->partnerOrder->closed_at != null ? $job->partnerOrder->closed_at->format('Y-m-d') : null);

            $formatted_job->put('due', (double) $job->partnerOrder->due);
            $formatted_job->put('has_pending_due', $this->hasDueJob($job) ? 1 : 0);
            $formatted_job->put('can_process', 0);
            $formatted_job->put('can_serve', 0);
            $formatted_job->put('can_collect', 0);
            $latest_pending_due_of_partner = $this->latestDueJob($job);
            $formatted_job->put('pending_due', $latest_pending_due_of_partner
                ? [
                    'resource_id' => $latest_pending_due_of_partner->resource_id,
                    'job_id' => $latest_pending_due_of_partner->id
                ]
                : null
            );

            $formatted_job->put('is_b2b', $this->isB2BJob($job) ? 1 : 0);
            if ($this->isB2BJob($job) || ($this->firstJobFromList && $this->shouldICheckActions($this->firstJobFromList, $job))) $formatted_job = $this->actionCalculator->calculateActionsForThisJob($formatted_job, $job);
            $formatted_jobs->push($formatted_job);
        }
        return $formatted_jobs;
    }

    private function isB2BJob($job)
    {
        return $job->partnerOrder->order->sales_channel === 'B2B';
    }

    private function hasDueJob($job)
    {
        return $job->partnerOrder->cancelled_at === null
            && $job->partnerOrder->closed_at !== null
            && $job->partnerOrder->closed_and_paid_at == null;
    }

    private function latestDueJob($job)
    {
        $partner = $job->partnerOrder->partner;
        if(!$partner) return null;
        $partner_order = $partner->partnerOrders()->NotB2bOrder()->closedButNotPaid()->notCancelled()->first();
        if(empty($partner_order)) return null;
        if($partner_order->getActiveJob()->id === $job->id) return null;
        return $partner_order->getActiveJob();
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

    private function formatHistoryJobs(Collection $jobs_grouped_by_years)
    {
        $formatted_history_jobs = collect();
        foreach ($jobs_grouped_by_years as $index => $jobs_of_a_year_grouped_by_months) {
            $year = collect();
            $year->put('value', $index);
            $year->put('months', collect());
            foreach ($jobs_of_a_year_grouped_by_months as $key => $jobs_of_a_month) {
                $month = collect();
                $month->put('value', $key);
                $month->put('jobs', $this->formatJobs($jobs_of_a_month));
                $year['months']->push($month);
            }
            $formatted_history_jobs->push($year);
        }
        return $formatted_history_jobs;
    }

    private function groupJobsByYearAndMonth($jobs)
    {
        $ungrouped_jobs = new Collection($jobs);
        return $ungrouped_jobs->groupBy('year')->transform(function ($item, $value) {
            return $item->groupBy('month');
        });
    }

    private function historyJobsFilterQuery($query)
    {
        $query = $query->join('partner_orders', 'partner_orders.id', '=', 'jobs.partner_order_id')->select('jobs.*', 'partner_orders.closed_at as closed_at', DB::raw('YEAR(closed_at) as year'), DB::raw('MONTH(closed_at) as month'));
        $query = $query->where('closed_at', '>=', Carbon::now()->subMonth(12));
        if ($this->month) $query = $query->whereYear('closed_at', '=', $this->year)->whereMonth('closed_at', '=', $this->month);
        return $query;
    }


    /**
     * @param Job|null $first_job
     * @param Job $job
     * @return bool
     */
    private function shouldICheckActions(Job $first_job, Job $job)
    {
        return $first_job && $first_job->schedule_date == $job->schedule_date &&
            $first_job->preferred_time == $job->preferred_time;
    }

    /**
     * @return array|null
     */
    public function getNextJobsInfo()
    {
        $jobs = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->tillNow()->get();
        $jobs = $jobs->filter(function ($job) {
            return $job->partnerOrder->order->sales_channel !== 'B2B';
        });
        $jobs = $this->loadNecessaryRelations($jobs);
        $jobs = $this->rearrange->rearrange($jobs);
        if (count($jobs) == 0) return null;
        $this->setFirstJobFromList($jobs->first());
        $next_jobs_count = $jobs->where('schedule_date', $this->firstJobFromList->schedule_date)
            ->where('preferred_time', $this->firstJobFromList->preferred_time)->count();
        if ($next_jobs_count == 0) return null;
        $preferred_time = Carbon::parse($this->firstJobFromList->preferred_time_start)->format('h:i A') . ' - ' . Carbon::parse($this->firstJobFromList->preferred_time_end)->format('h:i A');
        return ['preferred_time' => $preferred_time, 'jobs_count' => $next_jobs_count];
    }
}
