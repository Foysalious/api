<?php namespace Sheba\ResourceScheduler;

use App\Models\Job;
use App\Models\Resource;
use App\Models\ResourceSchedule as Schedule;
use Carbon\Carbon;
use Sheba\Repositories\ResourceScheduleRepository;

class ResourceHandler
{
    /** @var ResourceScheduleRepository */
    private $resourceSchedules;
    /** @var Resource */
    private $resource;

    /**
     * Handler constructor.
     * @param ResourceScheduleRepository $resource_schedules
     * @param Resource $resource
     */
    public function __construct(ResourceScheduleRepository $resource_schedules, Resource $resource)
    {
        $this->resourceSchedules = $resource_schedules;
        $this->resource = $resource;
    }

    /**
     * @param $date
     * @param $time
     * @return mixed
     */
    public function isAvailable($date, $time)
    {
        $date_time = Carbon::parse($date . ' ' . $time);
        return $this->resourceSchedules->filterByDateTime($this->resource, $date_time)->count() == 0;
    }

    /**
     * @param Job $job
     */
    public function book(Job $job)
    {
        $this->resourceSchedules->saveAgainstJob($job, $this->getBookingData($job));
    }

    /**
     * @param Job $job
     */
    public function reAssign(Job $job)
    {
        $this->resourceSchedules->updateAgainstJob($job, $this->getBookingData($job));
    }

    /**
     * @param Schedule $schedule
     * @param $min
     * @return bool
     */
    public function extend(Schedule $schedule, $min)
    {
        $extended_time = $schedule->end->addMinutes($min);
        $is_available_for_extend = ($this->resourceSchedules->filterByDateTime($this->resource, $extended_time)->count() == 0) ? true : false;

        if (!$is_available_for_extend)
            return false;

        return $this->resourceSchedules->updateAgainstJob($schedule->job, ['end' => $extended_time]);
    }

    private function getStartEndTimeFromJob(Job $job)
    {
        $category = $job->category_id ? $job->category : $job->service->category;
        $start = Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start);

        return [
            'start' => $start,
            'end' => $start->copy()->addMinutes($category->book_resource_minutes)
        ];
    }

    private function getBookingData(Job $job)
    {
        return $this->getStartEndTimeFromJob($job) + ['resource_id' => $this->resource->id];
    }
}